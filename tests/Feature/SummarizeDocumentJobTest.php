<?php

namespace Tests\Feature;

use App\Domains\Prompts\PromptMerge;
use App\Jobs\SummarizeDocumentJob;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use Tests\TestCase;

class SummarizeDocumentJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_summary_job(): void
    {

        $data = 'Foo bar';
        $dto = new \LlmLaraHub\LlmDriver\Responses\CompletionResponse($data);

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn($dto);

        $collection = Collection::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $document = Document::factory()->create([
            'summary' => null,
            'collection_id' => $collection->id,
        ]);

        //$this->fakeVerify($chat);

        $job = new SummarizeDocumentJob($document);
        $job->handle();

        $this->assertEquals('Foo bar', $document->refresh()->summary);
    }

    public function test_accepts_prompt(): void
    {

        $data = 'Foo bar';
        $dto = new \LlmLaraHub\LlmDriver\Responses\CompletionResponse($data);

        $prompt = 'Foo Bar [CONTEXT]';

        $shouldBe = PromptMerge::merge(
            ['[CONTEXT]'],
            ['Foo Baz'],
            $prompt);

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->withSomeOfArgs($shouldBe)
            ->andReturn($dto);

        $collection = Collection::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $document = Document::factory()->create([
            'summary' => null,
            'collection_id' => $collection->id,
        ]);

        DocumentChunk::factory()->create([
            'content' => 'Foo Baz',
            'document_id' => $document->id,
        ]);

        $job = new SummarizeDocumentJob($document, $prompt);
        $job->handle();

        $this->assertEquals('Foo bar', $document->refresh()->summary);
    }

    public function test_too_large(): void
    {

        $data = 'Foo bar';
        $dto = new \LlmLaraHub\LlmDriver\Responses\CompletionResponse($data);

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn($dto);

        $collection = Collection::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $document = Document::factory()->create([
            'summary' => null,
            'collection_id' => $collection->id,
        ]);

        //$this->fakeVerify($chat);

        $job = new SummarizeDocumentJob($document);
        $job->handle();

        $this->assertEquals('Foo bar', $document->refresh()->summary);
    }
}
