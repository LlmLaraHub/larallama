<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

        $dto = \App\LlmDriver\Responses\EmbeddingsResponseDto::from([
            'embedding' => data_get($embedding, 'data.0.embedding'),
            'token_count' => 1000
        ]);

        $this->assertInstanceOf(Vector::class, $dto->embedding);

    }
}
