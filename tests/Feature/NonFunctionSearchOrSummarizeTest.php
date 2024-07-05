<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use App\Models\Chat;
use App\Models\DocumentChunk;
use App\Models\Message;
use App\Models\Output;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
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

        DistanceQueryFacade::shouldReceive('cosineDistance')->once()->andReturn(DocumentChunk::all());

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

        $chat = Chat::factory()->create([
            'chatable_id' => $output->collection->id,
        ]);

        $message = Message::factory()->user()->create([
            'chat_id' => $chat->id,
            'meta_data' => MetaDataDto::from([
                'tool' => 'completion',
            ]),
        ]);
        $results = (new NonFunctionSearchOrSummarize())->handle($message);

        $this->assertNotNull($results->response);
        $this->assertNotNull($results->documentChunks);
    }

    /**
     * A basic feature test example.
     */
    public function test_results(): void
    {
        $documentChunk = DocumentChunk::factory()->create();

        DistanceQueryFacade::shouldReceive('cosineDistance')->once()->andReturn(DocumentChunk::all());

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

        $chat = Chat::factory()->create([
            'chatable_id' => $output->collection->id,
        ]);

        $message = Message::factory()->user()->create([
            'chat_id' => $chat->id,
            'body' => 'Search for foo',
            'meta_data' => MetaDataDto::from([
                'tool' => 'completion',
            ]),
        ]);

        $results = (new NonFunctionSearchOrSummarize())->handle($message);

        $this->assertNotNull($results->response);
        $this->assertNotNull($results->documentChunks);
    }

    public function test_no_search_no_summary()
    {

        DocumentChunk::factory()->create();

        DistanceQueryFacade::shouldReceive('cosineDistance')->once()->andReturn(DocumentChunk::all());

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

        $chat = Chat::factory()->create([
            'chatable_id' => $output->collection->id,
        ]);

        $message = Message::factory()->user()->create([
            'chat_id' => $chat->id,
            'body' => 'Search for foo',
            'meta_data' => MetaDataDto::from([
                'tool' => 'completion',
            ]),
        ]);

        $results = (new NonFunctionSearchOrSummarize())->handle($message);

        $this->assertNotNull($results->response);

    }

    public function test_with_prompt()
    {

        DocumentChunk::factory()->create();

        DistanceQueryFacade::shouldReceive('cosineDistance')->once()->andReturn(DocumentChunk::all());

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

        $chat = Chat::factory()->create([
            'chatable_id' => $output->collection->id,
        ]);

        $message = Message::factory()->user()->create([
            'chat_id' => $chat->id,
            'body' => 'Search for foo',
            'meta_data' => MetaDataDto::from([
                'tool' => 'completion',
            ]),
        ]);

        $results = (new NonFunctionSearchOrSummarize())->setPrompt('Foobar')->handle($message);

        $this->assertNotNull($results->response);

    }
}
