<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Prompts\DefaultPrompt;
use App\Domains\Prompts\SearchOrSummarize;
use App\Domains\Prompts\SummarizeDocumentPrompt;
use App\Domains\Prompts\SummarizePrompt;
use App\Models\Collection;
use App\Models\DocumentChunk;
use Facades\LlmLaraHub\LlmDriver\DistanceQuery;
use Illuminate\Support\Facades\Log;

class NonFunctionSearchOrSummarize
{
    protected string $results = "";

    public function handle(string $input, Collection $collection) : string
    {

        Log::info("[LaraChain] - Using the Non Function Search and Summarize Prompt", [
            'collection' => $collection->id,
            'input' => $input
        ]);

        $prompt = SearchOrSummarize::prompt($input);

        $response = LlmDriverFacade::driver(
            $collection->getDriver()
        )->completion($prompt);

        Log::info('[LaraChain] - Results from search or summarize', [
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

            //put_fixture("anonymous_embedding_result.json", $embedding);
            $documentChunkResults = DistanceQuery::distance(
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

            $contentFlattened = SummarizePrompt::prompt(
                originalPrompt: $input,
                context: $context
            );

            Log::info('[LaraChain] - Prompt with Context', [
                'prompt' => $contentFlattened,
            ]);

            $response = LlmDriverFacade::driver(
                $collection->getDriver()
            )->completion($contentFlattened);

            $this->results = $response->content;
        } elseif (str($response->content)->contains('summarize')) {
            Log::info('[LaraChain] - LLM Thinks it is summarize', [
                    'response' => $response->content]
            );

            $content = [];

            foreach ($collection->documents as $result) {
                $contentString = remove_ascii($result->summary);
                $content[] = $contentString; //reduce_text_size seem to mess up Claude?
            }

            $contentFlattened = implode(' ', $content);

            Log::info('[LaraChain] - Documents Flattened', [
                    'collection' => $collection->id,
                    'content' => $content]
            );

            $prompt = SummarizeDocumentPrompt::prompt($contentFlattened);

            $response = LlmDriverFacade::driver(
                $collection->getDriver()
            )->completion($prompt);


            $this->results = $response->content;
        } else {
            Log::info('[LaraChain] - LLM is not sure :(', [
                    'response' => $response->content]
            );

            $embedding = LlmDriverFacade::driver(
                $collection->getEmbeddingDriver()
            )->embedData($input);

            $embeddingSize = get_embedding_size($collection->getEmbeddingDriver());

            $documentChunkResults = DistanceQuery::distance(
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

            $response = LlmDriverFacade::driver(
                $collection->getDriver()
            )->completion($contentFlattened);


            $this->results = $response->content;

        }

        return $this->results;
    }
}
