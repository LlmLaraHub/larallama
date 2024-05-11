<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\File;
use LlmLaraHub\LlmDriver\DistanceQuery;
use Pgvector\Laravel\Vector;
use Tests\TestCase;

class DistanceQueryTest extends TestCase
{
    public function test_results()
    {
        $files = File::files(base_path('tests/fixtures/document_chunks'));
        $document = Document::factory()->create([
            'id' => 31,
        ]);

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true);
            DocumentChunk::factory()->create($data);
        }

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        $results = (new DistanceQuery())->distance(
            'embedding_1024',
            $document->collection_id,
            $vector);

        $this->assertCount(1, $results);

    }

    public function test_has_sibling_below()
    {
        $files = File::files(base_path('tests/fixtures/document_chunks'));
        $document = Document::factory()->create([
            'id' => 31,
        ]);

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true);
            DocumentChunk::factory()->create($data);
        }

        $documentSibling = DocumentChunk::where('guid', 'ffc97910f334c141b55af33b3c0b67c4')->first();

        $documentSibling->section_number = 0;

        $documentSibling->save();

        $nextSibling = DocumentChunk::factory()->create([
            'document_id' => 31,
            'sort_order' => $documentSibling->sort_order,
            'section_number' => 1,
            'guid' => 'ffc97910f334c141b55af33b3c0b67c4',
        ]);

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        $results = (new DistanceQuery())->distance(
            'embedding_1024',
            $document->collection_id,
            $vector);

        $this->assertCount(2, $results);

    }

    public function test_has_sibling_above_and_below()
    {
        $files = File::files(base_path('tests/fixtures/document_chunks'));
        $document = Document::factory()->create([
            'id' => 31,
        ]);

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true);
            DocumentChunk::factory()->create($data);
        }

        $documentSibling = DocumentChunk::where('guid', 'ffc97910f334c141b55af33b3c0b67c4')->first();

        $documentSibling->section_number = 1;

        $documentSibling->save();

        DocumentChunk::factory()->create([
            'document_id' => 31,
            'sort_order' => $documentSibling->sort_order,
            'section_number' => 0,
            'guid' => 'ffc97910f334c141b55af33b3c0b67c4',
        ]);

        DocumentChunk::factory()->create([
            'document_id' => 31,
            'sort_order' => $documentSibling->sort_order,
            'section_number' => 2,
            'guid' => 'ffc97910f334c141b55af33b3c0b67c4',
        ]);

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        $results = (new DistanceQuery())->distance(
            'embedding_1024',
            $document->collection_id,
            $vector);

        $this->assertCount(3, $results);

    }
}
