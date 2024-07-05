<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use App\Jobs\SimpleSearchAndSummarizeOrchestrateJob;
use App\Models\Chat;
use App\Models\DocumentChunk;
use App\Models\Message;
use Facades\LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;
use Tests\TestCase;

class SimpleSearchAndSummarizeOrchestrateJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_job(): void
    {
        $input = 'Foo bar';
        $chat = Chat::factory()->create();

        DocumentChunk::factory(3)->create();

        NonFunctionSearchOrSummarize::shouldReceive('handle')
            ->once()->andReturn(
                NonFunctionResponseDto::from([
                    'response' => 'Foobar',
                    'documentChunks' => DocumentChunk::all(),
                    'prompt' => 'Foo bar',
                ])
            );

        $message = Message::factory()->user()->create([
            'chat_id' => $chat->id,
            'meta_data' => MetaDataDto::from([
                'tool' => 'completion',
            ]),
        ]);

        (new SimpleSearchAndSummarizeOrchestrateJob($message))->handle();

        $this->assertDatabaseCount('messages', 1);
        $this->assertDatabaseCount('prompt_histories', 1);
        $this->assertDatabaseCount('message_document_references', 3);
    }
}
