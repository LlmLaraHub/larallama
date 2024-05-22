<?php

namespace Tests\Feature;

use App\Models\DocumentChunk;
use App\Models\Output;
use Facades\LlmLaraHub\LlmDriver\DistanceQuery;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use Pgvector\Laravel\Vector;
use Tests\TestCase;

class NonFunctionSearchOrSummarizeTest extends TestCase
{
    public function test_results_with_filter(): void
    {

        $documentChunk = DocumentChunk::factory()->create();

        DistanceQuery::shouldReceive('distance')->once()->andReturn(DocumentChunk::all());

        $output = Output::factory()->create([
            'active' => true,
            'public' => true,
        ]);

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->once()
            ->andReturn(EmbeddingsResponseDto::from(
                [
                    'embedding' => $vector,
                    'token_count' => 2,
                ]
            ));

        LlmDriverFacade::shouldReceive('driver->completion')
            ->twice()
            ->andReturn(CompletionResponse::from([
                'content' => 'search',
            ]));

        $results = (new NonFunctionSearchOrSummarize())->handle('Search for foo', $output->collection);

        $this->assertNotNull($results->response);
        $this->assertNotNull($results->documentChunks);
    }

    /**
     * A basic feature test example.
     */
    public function test_results(): void
    {
        $documentChunk = DocumentChunk::factory()->create();

        DistanceQuery::shouldReceive('distance')->once()->andReturn(DocumentChunk::all());

        $output = Output::factory()->create([
            'active' => true,
            'public' => true,
        ]);

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->once()
            ->andReturn(EmbeddingsResponseDto::from(
                [
                    'embedding' => $vector,
                    'token_count' => 2,
                ]
            ));

        LlmDriverFacade::shouldReceive('driver->completion')
            ->twice()
            ->andReturn(CompletionResponse::from([
                'content' => 'search',
            ]));

        $results = (new NonFunctionSearchOrSummarize())->handle('Search for foo', $output->collection);

        $this->assertNotNull($results->response);
        $this->assertNotNull($results->documentChunks);
    }

    public function test_no_search_no_summary()
    {

        DocumentChunk::factory()->create();

        DistanceQuery::shouldReceive('distance')->once()->andReturn(DocumentChunk::all());

        $output = Output::factory()->create([
            'active' => true,
            'public' => true,
        ]);

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->once()
            ->andReturn(EmbeddingsResponseDto::from(
                [
                    'embedding' => $vector,
                    'token_count' => 2,
                ]
            ));

        LlmDriverFacade::shouldReceive('driver->completion')
            ->twice()
            ->andReturn(CompletionResponse::from([
                'content' => 'not sure :(',
            ]));

        $results = (new NonFunctionSearchOrSummarize())->handle('Search for foo', $output->collection);

        $this->assertNotNull($results->response);

    }
}