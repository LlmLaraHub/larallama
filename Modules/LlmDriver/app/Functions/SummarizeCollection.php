<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Message;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class SummarizeCollection extends FunctionContract
{
    protected string $name = 'summarize_collection';

    protected string $description = 'NOT FOR SEARCH, This is used when the prompt wants to summarize the entire collection of documents';

    protected string $response = '';

    public array $toolTypes = [
        ToolTypes::ChatCompletion,
    ];

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] SummarizeCollection Function called');

        $summary = collect([]);

        foreach ($message->getChatable()->documents as $document) {
            $summary->add($document->summary);
        }

        notify_ui($message->getChat(), 'Getting Summary');

        $summary = $summary->implode('\n');

        Log::info('[LaraChain] SummarizeCollection', [
            'token_count_v2' => token_counter_v2($summary),
            'token_count_v1' => token_counter($summary),
        ]);

        $content = $message->getContent();

        $prompt = Templatizer::appendContext(true)
            ->handle(
                content: $content,
                replacement: $summary,
            );

        /**
         * @NOTE
         * I treat chat like completion
         * just same results
         */
        $results = LlmDriverFacade::driver($message->getDriver())
            ->setToolType(ToolTypes::NoFunction)
            ->completion($prompt);

        notify_ui($message->getChat(), 'Summary complete');

        return FunctionResponse::from([
            'content' => $results->content,
            'prompt' => $prompt,
            'requires_followup' => true,
            'documentChunks' => collect([]),
        ]);
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'prompt',
                description: 'The prompt the user is using the search for.',
                type: 'string',
                required: true,
            ),
        ];
    }
}
