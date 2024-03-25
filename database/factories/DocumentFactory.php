<?php

namespace Database\Factories;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => TypesEnum::random(),
            'status' => StatusEnum::random(),
            'summary' => $this->faker->text(),
            'file_path' => $this->faker->url(),
            'collection_id' => Collection::factory(),
        ];
    }

    public function pdf(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TypesEnum::PDF,
            ];
        });
    }
    
}
