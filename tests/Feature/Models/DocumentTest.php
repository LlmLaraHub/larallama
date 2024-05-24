<?php

namespace Tests\Feature\Models;

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
}
