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
}
