<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentChunk>
 */
class DocumentChunkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $embeddings = get_fixture('embedding_response.json');

        return [
            'guid' => fake()->uuid(),
            'content' => fake()->sentence(10),
            'original_content' => fake()->sentence(10),
            'document_id' => Document::factory(),
            'embedding' => data_get($embeddings, 'data.0.embedding'),
        ];
    }
}
