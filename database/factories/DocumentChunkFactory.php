<?php

namespace Database\Factories;

use App\Domains\Documents\StatusEnum;
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
            'status_embeddings' => StatusEnum::random(),
            'status_tagging' => StatusEnum::random(),
            'status_summary' => StatusEnum::random(),
            'original_content' => fake()->sentence(10),
            'summary' => fake()->sentence(5),
            'document_id' => Document::factory(),
            'embedding' => data_get($embeddings, 'data.0.embedding'),
        ];
    }
}
