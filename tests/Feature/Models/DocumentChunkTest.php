<?php

namespace Tests\Feature\Models;

use App\Domains\UnStructured\StructuredTypeEnum;
use App\Models\DocumentChunk;
use Tests\TestCase;

class DocumentChunkTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dc_factory()
    {
        $model = DocumentChunk::factory()->create();
        $this->assertNotNull($model->content);
        $this->assertNotNull($model->meta_data);
        $this->assertNotNull($model->section_number);
        $this->assertNotNull($model->sort_order);
        $this->assertInstanceOf(StructuredTypeEnum::class, $model->type);
    }

    public function test_original_boot()
    {
        $model = DocumentChunk::factory()->create([
            'content' => 'baz boo',
        ]);
        $model->update([
            'content' => 'foo bar',
        ]);
        $this->assertEquals('baz boo', $model->original_content);
    }

    public function test_dc_rel()
    {
        $model = DocumentChunk::factory()->create();
        $this->assertNotNull($model->document->id);
        $this->assertCount(1, $model->document->document_chunks);
        $this->assertNotNull($model->document->document_chunks()->first()->id);
    }

    public function test_embedding_dynamic()
    {

        $model = DocumentChunk::factory()->create();

        $embedding_column = $model->getEmbeddingColumn();

        $this->assertEquals('embedding_4096', $embedding_column);

        $model = DocumentChunk::factory()
            ->openAi()
            ->create();

        $embedding_column = $model->getEmbeddingColumn();

        $this->assertEquals('embedding_3072', $embedding_column);
    }
}
