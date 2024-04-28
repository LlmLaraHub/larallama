<?php

namespace Tests\Feature\Models;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\MessageDocumentReference;
use Tests\TestCase;

class MessageDocumentReferenceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = MessageDocumentReference::factory()->create();

        $this->assertNotNull($model->distance);
        $this->assertNotNull($model->message->id);
        $this->assertNotNull($model->document_chunk_id);
        $this->assertNotNull($model->document_chunk->id);
        $this->assertNotNull($model->message->message_document_references()->first()->id);
    }

    public function test_document_through()
    {
        $document = Document::factory()->create();

        $documentChunk = DocumentChunk::factory()->create([
            'document_id' => $document->id,
        ]);

        $model = MessageDocumentReference::factory()->create([
            'document_chunk_id' => $documentChunk->id,
        ]);

        $this->assertNotNull($model->document_chunk->document->id);

        $this->assertEquals($document->id, $model->document_chunk->document->id);
    }
}
