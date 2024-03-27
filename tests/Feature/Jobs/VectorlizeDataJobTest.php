<?php

namespace Tests\Feature\Jobs;

use App\Jobs\VectorlizeDataJob;
use App\LlmDriver\LlmDriverFacade;
use App\Models\DocumentChunk;
use Tests\TestCase;

class VectorlizeDataJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_data(): void
    {
        $embedding = get_fixture('embedding_response.json');

        $dto = new \App\LlmDriver\Responses\EmbeddingsResponseDto(
            data_get($embedding, 'data.0.embedding'),
            1000
        );

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->once()
            ->andReturn($dto);

        $documentChunk = DocumentChunk::factory()->create([
            'embedding' => null,
        ]);

        $job = new VectorlizeDataJob($documentChunk);
        $job->handle();

        $this->assertNotEmpty($documentChunk->embedding);
    }
}
