<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LlmLaraHub\TagFunction\Models\Tag;
use Tests\TestCase;

class DeleteDocumentsControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_delete(): void
    {

        $document = Document::factory()->create();
        $document->addTag("Fooobar");
        $documentChunk = DocumentChunk::factory()->create([
            'document_id' => $document->id
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->post(route("documents.delete"), [
            'documents' => [
                $document->id
            ]
        ])->assertSessionHasNoErrors()
        ->assertRedirect();

        $this->assertFalse(Document::exists());
        $this->assertFalse(DocumentChunk::exists());
        $this->assertFalse(Tag::exists());
    }
}
