<?php

namespace Tests\Feature;

use App\Models\DocumentChunk;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_config_helper(): void
    {

        $this->assertEquals('mock', driverHelper('mock', 'models.embedding_model'));

    }

    public function test_get_embedding_size(): void
    {

        $model = DocumentChunk::factory()->create();

        $embedding_column = get_embedding_size($model->getEmbeddingDriver());

        $this->assertEquals('embedding_4096', $embedding_column);

        $model = DocumentChunk::factory()
            ->openAi()
            ->create();

        $embedding_column = get_embedding_size($model->getEmbeddingDriver());

        $this->assertEquals('embedding_3072', $embedding_column);

    }
}
