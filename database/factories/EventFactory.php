<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'start_date' => $this->faker->date,
            'start_time' => $this->faker->time,
            'end_date' => $this->faker->date,
            'end_time' => $this->faker->time,
            'location' => $this->faker->sentence,
            'type' => \App\Domains\Events\EventTypes::Event,
            'assigned_to_id' => User::factory(),
            'assigned_to_assistant' => false,
            'all_day' => false,
            'collection_id' => Collection::factory(),
        ];
    }
}
