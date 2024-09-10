<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'details' => $this->faker->paragraph(),
            'completed_at' => $this->faker->date(),
            'due_date' => $this->faker->date(),
            'assistant' => false,
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
        ];
    }
}
