<?php

namespace App\Http\Controllers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Prompts\AnonymousChat;
use App\Domains\Prompts\SummarizeForPage;
use App\Models\Collection;
use App\Models\Output;
use Facades\LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Helpers\TrimText;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;

class WebPageOutputController extends OutputController
{
    protected function getChatMessages(): array
    {
        $messages = request()->session()->only(['messages']);

        if (empty($messages)) {
            $messages = MessageInDto::from([
                'content' => AnonymousChat::system(),
                'role' => 'system',
                'is_ai' => true,
                'show' => false,
            ]);

            request()->session()->push('messages', $messages);
            $messages = Arr::wrap($messages);
        }

        return $messages;
    }

    protected function setChatMessages(string $input, string $role = 'user')
    {
        $this->getChatMessages();

        $messages = MessageInDto::from([
            'content' => str($input)->markdown(),
            'role' => $role,
            'is_ai' => $role !== 'user',
            'show' => $role !== 'system',
        ]);

        request()->session()->push('messages', $messages);

        return $messages;
    }

    public function chat(Output $output)
    {
        if (! auth()->check() && ! $output->public) {
            abort(404);
        }

        if (! $output->active) {
            abort(404);
        }

        $validated = request()->validate([
            'input' => 'required|string',
        ]);

        Log::info('[LaraChain] - Message Coming in', [
            'message' => $validated['input']]
        );

        $input = $validated['input'];

        $this->setChatMessages($input, 'user');

        Log::info('[LaraChain] - Check if Search or Summarize', [
            'message' => $validated['input']]
        );

        /** @var NonFunctionResponseDto $results */
        $results = NonFunctionSearchOrSummarize::handle($input, $output->collection);

        $this->setChatMessages($results->response, 'assistant');

        return back();
    }

    public function generateSummaryFromCollection(Collection $collection)
    {

        Log::info('[LaraChain] - Getting Summary');
        $summary = collect([]);
        foreach ($collection->documents as $document) {
            $chunk = (new TrimText())->handle($document->summary);
            $summary->add($chunk);
        }

        $summary = $summary->implode('\n');

        notify_collection_ui(
            collection: $collection,
            status: CollectionStatusEnum::PENDING,
            message: 'Sending Collection Summary to LLM'
        );

        $prompt = SummarizeForPage::prompt($summary);

        $results = LlmDriverFacade::driver($collection->getDriver())->completion($prompt);

        Log::info('[LaraChain] - Page Summary Created', [
            'summary' => $results->content,
        ]);

        return response()->json([
            'summary' => $results->content,
        ]);
    }
}
