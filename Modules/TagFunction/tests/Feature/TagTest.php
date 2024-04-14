<?php

namespace LlmLaraHub\TagFunction\tests\Feature;

use App\Models\Document;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LlmLaraHub\TagFunction\Models\Tag;

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

    public function test_add_tag_new() {
        $document = Document::factory()->create();
        $tag = Tag::factory()->create();

        $this->assertDatabaseCount('tags', 1);
        $document->addTag($tag->name);

        $this->assertNotEmpty($document->tags);
        $this->assertDatabaseCount('tags', 1);

    }

    public function test_add_tag_existing() {
        $document = Document::factory()->create();

        $this->assertDatabaseCount('tags', 0);
        $document->addTag("foobar");

        $this->assertNotEmpty($document->tags);
        $this->assertDatabaseCount('tags', 1);
    }
}
