<?php

namespace Tests\Feature;

use Pgvector\Laravel\Vector;
use Tests\TestCase;

class EmbeddingsResponseDtoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {

        $embedding = get_fixture('embedding_response.json');

        $dto = \LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto::from([
            'embedding' => data_get($embedding, 'data.0.embedding'),
            'token_count' => 1000,
        ]);

        $this->assertInstanceOf(Vector::class, $dto->embedding);

    }
}
