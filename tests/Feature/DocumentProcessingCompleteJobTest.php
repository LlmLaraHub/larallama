<?php

namespace Tests\Feature;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Events\CollectionStatusEvent;
use App\Jobs\DocumentProcessingCompleteJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DocumentProcessingCompleteJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        Event::fake();
        $document = Document::factory()->create([
            'status' => StatusEnum::Pending,
        ]);

        DocumentChunk::factory()->count(5)->create([
            'document_id' => $document->id,
            'section_number' => 0,
        ]);

        Bus::fake();

        $job = new DocumentProcessingCompleteJob($document, null);
        $job->handle();

        $this->assertEquals($document->refresh()->status, StatusEnum::Complete);

        $this->assertEquals($document->refresh()->document_chunk_count, 5);

        Event::assertDispatched(function (CollectionStatusEvent $event) use ($document) {
            return $event->collection->id === $document->collection->id
                && $event->status === CollectionStatusEnum::PROCESSED;
        });
    }
}
