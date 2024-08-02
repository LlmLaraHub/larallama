<?php

namespace Tests\Feature\Models;

use App\Models\Document;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = \App\Models\Document::factory()->create();

        $this->assertNotNull($model->collection_id);
        $this->assertNotNull($model->collection->id);
        $this->assertCount(1, $model->collection->documents);
        $this->assertNotNull($model->collection->documents()->first()->id);
        $this->assertEquals(
            $model->summary,
            $model->content
        );
    }

    public function test_parent()
    {
        $modelParent = \App\Models\Document::factory()->create();

        $model = \App\Models\Document::factory()->create([
            'parent_id' => $modelParent->id,
        ]);

        $this->assertEquals($modelParent->id,
            $model->parent->id);
    }

    public function test_document_make()
    {
        $collection = \App\Models\Collection::factory()->create();
        $document = Document::make('Foo bar',
            $collection);
        $this->assertNotNull($document->id);
        $this->assertNotNull($document->collection_id);
        $this->assertNotNull($document->collection->id);
        $this->assertCount(1, $document->collection->documents);
        $this->assertNotNull($document->collection->documents()->first()->id);
    }

    public function test_document_vectorize()
    {
        Bus::fake();
        $collection = \App\Models\Collection::factory()->create();
        $document = Document::make('Foo bar',
            $collection);
        $document->vectorizeDocument();
        Bus::assertBatchCount(1);
    }
}
