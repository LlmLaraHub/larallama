<?php

namespace LlmLaraHub\LlmDriver\Tests\Feature;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Filter;
use Illuminate\Support\Facades\File;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\DistanceQuery\Drivers\PostGres;
use Pgvector\Laravel\Vector;
use Tests\TestCase;

class DistanceQueryClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_distance_query(): void
    {

        $results = DistanceQueryFacade::driver('post_gres');

        $this->assertInstanceOf(PostGres::class, $results);

    }

    public function test_mock_results()
    {
        $files = File::files(base_path('tests/fixtures/document_chunks'));

        $document = Document::factory()->create([
            'id' => 31,
        ]);

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true);
            DocumentChunk::factory()->create($data);
        }

        $filter = Filter::factory()->create([
            'collection_id' => $document->collection_id,
        ]);

        $filter->documents()->attach($document->id);

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        $results = DistanceQueryFacade::driver('mock')->cosineDistance(
            'embedding_1024',
            $document->collection_id,
            $vector,
            $filter);

        $this->assertCount(1, $results);

    }

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

        $filter = Filter::factory()->create([
            'collection_id' => $document->collection_id,
        ]);

        $filter->documents()->attach($document->id);

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        $results = DistanceQueryFacade::cosineDistance(
            'embedding_1024',
            $document->collection_id,
            $vector,
            $filter);

        $this->assertCount(1, $results);

    }

    public function test_results_empty_due_to_filter()
    {
        $files = File::files(base_path('tests/fixtures/document_chunks'));

        $document = Document::factory()->create([
            'id' => 31,
        ]);

        $documentNot = Document::factory()->create();

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true);
            DocumentChunk::factory()->create($data);
        }

        $filter = Filter::factory()->create([
            'collection_id' => $documentNot->collection_id,
        ]);

        $filter->documents()->attach($documentNot->id);

        $question = get_fixture('embedding_question_distance.json');

        $vector = new Vector($question);

        $results = DistanceQueryFacade::cosineDistance(
            'embedding_1024',
            $document->collection_id,
            $vector,
            $filter);

        $this->assertCount(0, $results);

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

        $results = DistanceQueryFacade::cosineDistance(
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

        $results = DistanceQueryFacade::cosineDistance(
            'embedding_1024',
            $document->collection_id,
            $vector);

        $this->assertCount(3, $results);

    }
}
