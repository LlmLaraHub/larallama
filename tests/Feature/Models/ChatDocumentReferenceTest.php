<?php

namespace Tests\Feature\Models;

use App\Models\ChatDocumentReference;
use Tests\TestCase;

class ChatDocumentReferenceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = ChatDocumentReference::factory()->create();

        $this->assertIsString($model->reference);
        $this->assertNotNull($model->chat->id);
        $this->assertNotNull($model->document->id);
        $this->assertNotNull($model->document_chunk->id);
        $this->assertNotNull($model->chat->chat_document_references()->first()->id);
    }
}
