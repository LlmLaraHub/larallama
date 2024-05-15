<?php

namespace App\Http\Controllers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\AnonymousChat;
use App\Domains\Prompts\DefaultPrompt;
use App\Domains\Prompts\SearchOrSummarize;
use App\Domains\Prompts\SummarizeDocumentPrompt;
use App\Domains\Prompts\SummarizeForPage;
use App\Domains\Prompts\SummarizePrompt;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\PublicOutputResource;
use App\Models\Collection;
use App\Models\DocumentChunk;
use App\Models\Output;
use Facades\LlmLaraHub\LlmDriver\DistanceQuery;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Helpers\TrimText;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;

class WebPageOutputController extends Controller
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

        $prompt = SearchOrSummarize::prompt($input);

        $response = LlmDriverFacade::driver(
            $output->collection->getDriver()
        )->completion($prompt);

        Log::info('[LaraChain] - Results from search or summarize', [
            'results' => $response->content,
        ]);

        if (str($response->content)->contains('search')) {
            Log::info('[LaraChain] - LLM Thinks it is Search', [
                'response' => $response->content]
            );

            $embedding = LlmDriverFacade::driver(
                $output->collection->getEmbeddingDriver()
            )->embedData($input);

            $embeddingSize = get_embedding_size($output->collection->getEmbeddingDriver());

            //put_fixture("anonymous_embedding_result.json", $embedding);
            $documentChunkResults = DistanceQuery::distance(
                $embeddingSize,
                $output->collection->id,
                $embedding->embedding
            );

            $content = [];

            /** @var DocumentChunk $result */
            foreach ($documentChunkResults as $result) {
                $contentString = remove_ascii($result->content);
                $content[] = $contentString; //reduce_text_size seem to mess up Claude?
            }

            $context = implode(' ', $content);

            Log::info('[LaraChain] - Content Found', [
                'content' => $content,
            ]);

            $contentFlattened = SummarizePrompt::prompt(
                originalPrompt: $input,
                context: $context
            );

            Log::info('[LaraChain] - Prompt with Context', [
                'prompt' => $contentFlattened,
            ]);

            $response = LlmDriverFacade::driver(
                $output->collection->getDriver()
            )->completion($contentFlattened);

            $this->setChatMessages($response->content, 'assistant');

        } elseif (str($response->content)->contains('summarize')) {
            Log::info('[LaraChain] - LLM Thinks it is summarize', [
                'response' => $response->content]
            );

            foreach ($output->collection->documents as $result) {
                $contentString = remove_ascii($result->summary);
                $content[] = $contentString; //reduce_text_size seem to mess up Claude?
            }

            $contentFlattened = implode(' ', $content);

            Log::info('[LaraChain] - Documents Flattened', [
                'collection' => $output->collection_id,
                'content' => $content]
            );

            $prompt = SummarizeDocumentPrompt::prompt($contentFlattened);

            $response = LlmDriverFacade::driver(
                $output->collection->getDriver()
            )->completion($prompt);

            $this->setChatMessages($response->content, 'assistant');
        } else {
            Log::info('[LaraChain] - LLM is not sure :(', [
                'response' => $response->content]
            );

            $embedding = LlmDriverFacade::driver(
                $output->collection->getEmbeddingDriver()
            )->embedData($input);

            $embeddingSize = get_embedding_size($output->collection->getEmbeddingDriver());

            $documentChunkResults = DistanceQuery::distance(
                $embeddingSize,
                $output->collection->id,
                $embedding->embedding
            );

            $content = [];

            /** @var DocumentChunk $result */
            foreach ($documentChunkResults as $result) {
                $contentString = remove_ascii($result->content);
                $content[] = $contentString; //reduce_text_size seem to mess up Claude?
            }

            $context = implode(' ', $content);

            Log::info('[LaraChain] - Content Found', [
                'content' => $content,
            ]);

            $contentFlattened = DefaultPrompt::prompt(
                originalPrompt: $input,
                context: $context
            );

            $response = LlmDriverFacade::driver(
                $output->collection->getDriver()
            )->completion($contentFlattened);

            $this->setChatMessages($response->content, 'assistant');

        }

        return back();
    }

    public function create(Collection $collection)
    {
        return inertia('Outputs/WebPage/Create', [
            'collection' => new CollectionResource($collection),
        ]);
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

    public function store(Collection $collection)
    {

        $validated = request()->validate([
            'title' => 'required|string',
            'summary' => 'required|string',
            'active' => 'boolean|nullable',
            'public' => 'boolean|nullable',
        ]);

        Output::create([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'collection_id' => $collection->id,
            'active' => data_get($validated, 'active', false),
            'public' => data_get($validated, 'public', false),
            'type' => OutputTypeEnum::WebPage,
            'meta_data' => [],
        ]);

        request()->session()->flash('flash.banner', 'Web page output added successfully');

        return to_route('collections.outputs.index', $collection);
    }

    public function edit(Collection $collection, Output $output)
    {
        return inertia('Outputs/WebPage/Edit', [
            'output' => $output,
            'collection' => new CollectionResource($collection),
        ]);
    }

    public function update(Collection $collection, Output $output)
    {
        $validated = request()->validate([
            'title' => 'required|string',
            'summary' => 'required|string',
            'active' => 'boolean|nullable',
            'public' => 'boolean|nullable',
        ]);

        $output->update($validated);

        request()->session()->flash('flash.banner', 'Updated');

        return back();
    }

    public function show(Output $output)
    {
        if (! auth()->check() && ! $output->public) {
            abort(404);
        }

        if (! $output->active) {
            abort(404);
        }

        return inertia('Outputs/WebPage/Show', [
            'output' => new PublicOutputResource($output),
            'messages' => data_get($this->getChatMessages(), 'messages', []),
        ]);
    }
}
