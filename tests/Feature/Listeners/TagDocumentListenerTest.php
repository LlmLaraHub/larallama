<?php

namespace Tests\Feature\Listeners;

use App\Events\DocumentParsedEvent;
use App\Listeners\TagDocumentListener;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class TagDocumentListenerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_tagging(): void
    {

        LlmDriverFacade::shouldReceive('driver->completion')
            ->times(3)
            ->andReturn(
                CompletionResponse::from([
                    'content' => 'Tag 1, Tag Two Test, Tag Three Test',
                ])
            );

        $document = Document::factory()->create();

        $documentChunk1 = DocumentChunk::factory()->create([
            'document_id' => $document->id,
            'content' => 'Foo bar',
        ]);

        $documentChunk2 = DocumentChunk::factory()->create([
            'content' => 'Baz qux',
            'document_id' => $document->id,
        ]);

        Bus::fake();

        $batch = $this->mock(Batch::class);

        $event = new DocumentParsedEvent(
            $document);

        $listener = new TagDocumentListener();
        $listener->handle($event);

        $this->assertDatabaseHas('tags', [
            'name' => 'Tag 1',
        ]);

        $this->assertCount(3, $document->tags);

    }
}
