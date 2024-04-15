<?php

namespace LlmLaraHub\TagFunction\tests\Feature;

use App\Models\Document;
use App\Models\DocumentChunk;
use LlmLaraHub\TagFunction\Models\Tag;
use Tests\TestCase;

class TagTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_tag_model(): void
    {
        $document = Document::factory()
            ->has(Tag::factory(), 'tags')->create();

        $this->assertNotEmpty($document->tags);

        $tag = $document->tags()->first();

        $this->assertEquals(
            $document->id,
            $tag->documents->first()->id
        );
    }

    public function test_add_tag_new()
    {
        $document = Document::factory()->create();
        $tag = Tag::factory()->create();

        $this->assertDatabaseCount('tags', 1);
        $document->addTag($tag->name);

        $this->assertNotEmpty($document->tags);
        $this->assertDatabaseCount('tags', 1);

    }

    public function test_add_tag_existing()
    {
        $document = Document::factory()->create();

        $this->assertDatabaseCount('tags', 0);
        $document->addTag('foobar');

        $this->assertNotEmpty($document->tags);
        $this->assertDatabaseCount('tags', 1);
    }

    public function test_sibling_tags()
    {
        $document = Document::factory()->create();

        $documentChunk = DocumentChunk::factory()->create([
            'document_id' => $document->id,
        ]);

        $documentChunk->addTag('foobar1');

        $documentChunk = DocumentChunk::factory()->create([
            'document_id' => $document->id,
        ]);

        $this->assertCount(2, $document->refresh()->document_chunks);
        $documentChunk->addTag('foobar1');
        $documentChunk->addTag('foobar2');

        $tags = $document->siblingTags();

        $this->assertCount(2, $tags);

        $this->assertTrue(in_array('foobar1', $document->siblingTags()));
    }
}
