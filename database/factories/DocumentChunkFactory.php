<?php

namespace Database\Factories;

use App\Domains\Documents\StatusEnum;
use App\Domains\UnStructured\StructuredDto;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Models\Collection;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use LlmLaraHub\LlmDriver\DriversEnum;

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

        /**
         * @TODO
         * Someone had a late night idea
         * that he never finished
         */
        $dto = StructuredDto::from([
            'type' => StructuredTypeEnum::Narrative,
            'content' => 'content',
            'title' => 'test title',
            'page' => 'page',
            'guid' => 'guid',
            'file_name' => 'file_name',
            'created_by' => 'Bob Belcher',
            'last_updated_by' => 'Bob Belcher',
            'created_at' => '1713385302',
            'description' => fake()->sentences(3, true),
            'subject' => fake()->sentences(3, true),
            'keywords' => fake()->sentences(3, true),
            'category' => fake()->sentences(3, true),
            'updated_at' => '1713792670',
            'coordinates' => 'coordinates',
            'element_depth' => 'elment_depth',
            'is_continuation' => false,
            'parent_id' => null,
        ]);

        return [
            'guid' => fake()->uuid(),
            'content' => fake()->sentence(10),
            'sort_order' => fake()->numberBetween(1, 100),
            'section_number' => fake()->numberBetween(1, 100),
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
            'meta_data' => $dto->toArray(),
            'type' => StructuredTypeEnum::Raw,
        ];
    }

    public function openAi(): Factory
    {

        return $this->state(function (array $attributes) {
            $collection = Collection::factory()->create([
                'driver' => DriversEnum::OpenAi,
                'embedding_driver' => DriversEnum::OpenAi,
            ]);
            $document = Document::factory()->create([
                'collection_id' => $collection->id,
            ]);

            return [
                'document_id' => $document->id,
            ];
        });
    }

    public function ollama(): Factory
    {

        return $this->state(function (array $attributes) {
            $collection = Collection::factory()->create([
                'driver' => DriversEnum::Ollama,
                'embedding_driver' => DriversEnum::Ollama,
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
