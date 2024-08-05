<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Prompts\DefaultPrompt;
use App\Domains\Prompts\SearchOrSummarize;
use App\Domains\Prompts\SummarizeDocumentPrompt;
use App\Domains\Prompts\SummarizePrompt;
use App\Models\Collection;
use App\Models\DocumentChunk;
use App\Models\Message;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;

class NonFunctionSearchOrSummarize
{
    protected string $prompt = '';

    public function setPrompt(string $prompt): self
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function handle(
        Message $message
    ): NonFunctionResponseDto {
        $collection = $message->getChatable();

        if (get_class($collection) !== Collection::class) {
            throw new \Exception('Can only do Collection class right now');
        }

        $filter = $message->getFilter();

        Log::info('[LaraChain] - Using the NonFunctionSearchOrSummarize Search and Summarize Prompt', [
            'collection' => $collection->id,
            'input' => $message->getContent(),
            'filters' => $filter?->toArray(),
            'prompt' => $this->prompt,
        ]);

        $prompt = SearchOrSummarize::prompt($message->getContent());

        $response = LlmDriverFacade::driver(
            $message->getDriver()
        )->completion($prompt);

        Log::info('[LaraChain] - Results from NonFunctionSearchOrSummarize from search or summarize ', [
            'results' => $response->content,
        ]);

        if (str($response->content)->contains('search')) {
            Log::info('[LaraChain] - LLM Thinks it is Search', [
                'response' => $response->content]
            );

            $embedding = LlmDriverFacade::driver(
                $collection->getEmbeddingDriver()
            )->embedData($message->getContent());

            $embeddingSize = get_embedding_size($collection->getEmbeddingDriver());

            $documentChunkResults = DistanceQueryFacade::cosineDistance(
                $embeddingSize,
                $collection->id,
                $embedding->embedding,
                $message->meta_data
            );

            $content = [];

            /** @var DocumentChunk $result */
            foreach ($documentChunkResults as $result) {
                $contentString = remove_ascii($result->content);
                $content[] = $contentString; //reduce_text_size seem to mess up Claude?
            }

            $context = implode(' ', $content);

            if ($this->prompt !== '') {
                $contentFlattened = Templatizer::appendContext(true)
                    ->handle($this->prompt, $context);
            } else {
                $contentFlattened = SummarizePrompt::prompt(
                    originalPrompt: $message->getContent(),
                    context: $context
                );
            }

            Log::info('[LaraChain] - Prompt with Context', [
                'prompt' => $contentFlattened,
            ]);

            /**
             * @TODO Make this chat
             */
            $response = LlmDriverFacade::driver(
                $message->getDriver()
            )->completion($contentFlattened);

            if ($message->getChat()) {
                notify_ui($message->getChat(), 'Complete');
            }

            return NonFunctionResponseDto::from(
                [
                    'response' => $response->content,
                    'documentChunks' => $documentChunkResults,
                    'prompt' => $contentFlattened,
                ]
            );
        } elseif (str($response->content)->contains('summarize')) {
            Log::info('[LaraChain] - LLM Thinks it is summarize', [
                'response' => $response->content]
            );

            $content = [];

            foreach ($collection->documents as $result) {
                $contentString = remove_ascii($result->summary);
                $content[] = $contentString; //reduce_text_size seem to mess up Claude?
            }

            $context = implode(' ', $content);

            if ($this->prompt !== '') {
                $contentFlattened = Templatizer::appendContext(true)
                    ->handle($this->prompt, $context);
            } else {
                $contentFlattened = $prompt = SummarizeDocumentPrompt::prompt($context);
            }

            Log::info('[LaraChain] - Documents Flattened', [
                'collection' => $collection->id,
                'content' => $content]
            );

            $response = LlmDriverFacade::driver(
                $collection->getDriver()
            )->completion($contentFlattened);

            if ($collection->getChat()) {
                notify_ui($collection->getChat(), 'Complete');
            }

            return NonFunctionResponseDto::from(
                [
                    'response' => $response->content,
                    'documentChunks' => collect(),
                    'prompt' => $prompt,
                ]
            );
        } else {
            Log::info('[LaraChain] - LLM is not sure :(', [
                'response' => $response->content]
            );

            $embedding = LlmDriverFacade::driver(
                $collection->getEmbeddingDriver()
            )->embedData($message->getContent());

            $embeddingSize = get_embedding_size($collection->getEmbeddingDriver());

            $documentChunkResults = DistanceQueryFacade::cosineDistance(
                $embeddingSize,
                $collection->id,
                $embedding->embedding,
                $message->meta_data
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
                originalPrompt: $message->getContent(),
                context: $context
            );

            /**
             * @TODO Make this chat
             */
            $response = LlmDriverFacade::driver(
                $collection->getDriver()
            )->completion($contentFlattened);

            if ($collection->getChat()) {
                notify_ui($collection->getChat(), 'Complete');
            }

            return NonFunctionResponseDto::from(
                [
                    'response' => $response->content,
                    'documentChunks' => collect(),
                    'prompt' => $contentFlattened,
                ]
            );

        }

    }
}
