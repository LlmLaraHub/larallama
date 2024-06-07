<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Prompts\DefaultPrompt;
use App\Domains\Prompts\PromptMerge;
use App\Domains\Prompts\SearchOrSummarize;
use App\Domains\Prompts\SummarizeDocumentPrompt;
use App\Domains\Prompts\SummarizePrompt;
use App\Models\Collection;
use App\Models\DocumentChunk;
use App\Models\Filter;
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

    public function handle(string $input,
        HasDrivers $collection,
        ?Filter $filter = null): NonFunctionResponseDto
    {
        $collection = $collection->getChatable();

        if (! get_class($collection) === Collection::class) {
            throw new \Exception('Can only do Collection class right now');
        }

        Log::info('[LaraChain] - Using the NonFunctionSearchOrSummarize Search and Summarize Prompt', [
            'collection' => $collection->id,
            'input' => $input,
            'filters' => $filter?->toArray(),
            'prompt' => $this->prompt,
        ]);

        $prompt = SearchOrSummarize::prompt($input);

        $response = LlmDriverFacade::driver(
            $collection->getDriver()
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
            )->embedData($input);

            $embeddingSize = get_embedding_size($collection->getEmbeddingDriver());

            $documentChunkResults = DistanceQueryFacade::cosineDistance(
                $embeddingSize,
                $collection->id,
                $embedding->embedding,
                $filter
            );

            $content = [];

            /** @var DocumentChunk $result */
            foreach ($documentChunkResults as $result) {
                $contentString = remove_ascii($result->content);
                $content[] = $contentString; //reduce_text_size seem to mess up Claude?
            }

            $context = implode(' ', $content);

            if ($this->prompt !== '') {
                $contentFlattened = PromptMerge::merge([
                    '[CONTEXT]',
                ], [
                    $context,
                ], $this->prompt);
            } else {
                $contentFlattened = SummarizePrompt::prompt(
                    originalPrompt: $input,
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
                $collection->getDriver()
            )->completion($contentFlattened);

            if ($collection->getChat()) {
                notify_ui($collection->getChat(), 'Complete');
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
                $contentFlattened = PromptMerge::merge([
                    '[CONTEXT]',
                ], [
                    $context,
                ], $this->prompt);
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
            )->embedData($input);

            $embeddingSize = get_embedding_size($collection->getEmbeddingDriver());

            $documentChunkResults = DistanceQueryFacade::cosineDistance(
                $embeddingSize,
                $collection->id,
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
