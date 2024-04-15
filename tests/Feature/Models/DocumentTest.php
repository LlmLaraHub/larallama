<?php

namespace Tests\Feature\Models;

use LlmLaraHub\TagFunction\Models\Tag;
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
    }

   
}
