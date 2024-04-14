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
}
