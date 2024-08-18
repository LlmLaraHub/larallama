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
        $start = now()->subDays(rand(1, 10));

        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'start_date' => $start->format('Y-m-d'),
            'start_time' => now()->format('H:i:s'),
            'end_date' => $start->addDays(rand(1, 10))->format('Y-m-d'),
            'end_time' => now()->format('H:i:s'),
            'location' => $this->faker->sentence,
            'type' => \App\Domains\Events\EventTypes::Event,
            'assigned_to_id' => User::factory(),
            'assigned_to_assistant' => false,
            'all_day' => false,
            'collection_id' => Collection::factory(),
        ];
    }
}
