<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'response' => $this->faker->paragraph(),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'document_id' => Document::factory(),
            'report_id' => Report::factory(),
            'prompt' => $this->faker->sentence(),
        ];
    }
}
