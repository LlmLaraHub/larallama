<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use LlmLaraHub\LlmDriver\DriversEnum;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'active' => $this->faker->boolean,
            'team_id' => Team::factory(),
            'summary_prompt' => '<role>Assisant building summary of the document<task>',
            'driver' => DriversEnum::Mock,
            'embedding_driver' => DriversEnum::Mock,
        ];
    }
}
