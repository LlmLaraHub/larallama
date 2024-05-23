<?php

namespace Tests\Feature\Models;

use App\Models\Transformer;
use Tests\TestCase;

class TransformerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_transformer(): void
    {

        $model = Transformer::factory()->create();

        $this->assertNotNull($model->transformable->id);
        $this->assertNotNull($model->transformable->transformers()->first()->id);
    }
}
