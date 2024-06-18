<?php

namespace Tests\Feature;

use App\Domains\EmailParser\MailDto;
use App\Domains\Outputs\OutputTypeEnum;
use App\Jobs\EmailReplyOutputJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Output;
use Illuminate\Support\Facades\Mail;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use Pgvector\Laravel\Vector;
use Tests\TestCase;

class EmailReplyOutputJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_replies(): void
    {
        Mail::fake();

        $output = Output::factory()
            ->emailSecrets()
            ->create([
                'type' => OutputTypeEnum::EmailReplyOutput,
            ]);

        $mailDto = MailDto::from([
            'to' => 'info+12345@llmassistant.io',
            'from' => 'foo@var.com',
            'subject' => 'This is it',
            'header' => 'This is header',
            'body' => 'This is the body',
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
            ->once()
            ->andReturn(
                CompletionResponse::from([
                    'content' => 'This is the content',
                ])
            );

        $document = Document::factory()->create([
            'collection_id' => $output->collection->id,
        ]);

        $documentChunk = DocumentChunk::factory()->create([
            'document_id' => $document->id,
        ]);

        DistanceQueryFacade::shouldReceive('cosineDistance')
            ->once()
            ->andReturn([
                $documentChunk,
            ]);

        [$job, $batch] = (new EmailReplyOutputJob($output, $mailDto))->withFakeBatch();

        $job->handle();

        Mail::assertSentCount(1);
    }
}
