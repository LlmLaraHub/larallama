<?php

namespace Tests\Feature;

use App\Helpers\TextChunker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TextChunkerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_chunking(): void
    {
        $text = get_fixture("chunkable_text.txt", false);

        $results = TextChunker::handle($text);

        $this->assertCount(4, $results);

        $text = get_fixture("chunkable_text.txt", false);

        $results = TextChunker::handle($text, 300, 100);

        $this->assertCount(9, $results);

        //put_fixture("chunkable_text_results.json", $results);
        $this->assertEquals(get_fixture("chunkable_text_results.json"), $results);
    }
}
