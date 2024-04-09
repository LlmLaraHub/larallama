<?php

namespace Database\Factories;

use App\Domains\Documents\StatusEnum;
use App\LlmDriver\DriversEnum;
use App\Models\Collection;
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
            'embedding_3072' => data_get($embeddings, 'data.0.embedding'),
            'embedding_1536' => null,
            'embedding_2048' => null,
            'embedding_4096' => null,
        ];
    }

    public function openAi(): Factory
    {

        return $this->state(function (array $attributes) {
            $collection = Collection::factory()->create([
                'driver' => DriversEnum::OpenAi,
            ]);
            $document = Document::factory()->create([
                'collection_id' => $collection->id,
            ]);
            return [
                'document_id' => $document->id,
            ];
        });
    }

}
