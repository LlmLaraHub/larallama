<?php

namespace Tests\Feature\Models;

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

        $this->assertIsString($model->reference);
        $this->assertNotNull($model->distance);
        $this->assertNotNull($model->message->id);
        $this->assertNotNull($model->document_chunk->id);
        $this->assertNotNull($model->message->message_document_references()->first()->id);
    }
}
