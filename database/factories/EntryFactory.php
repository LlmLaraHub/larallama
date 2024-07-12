<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entry>
 */
class EntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'type' => \App\Domains\Reporting\EntryTypeEnum::Solution,
            'votes' => 0,
            'section_id' => Section::factory(),
            'document_id' => Document::factory(),
        ];
    }
}
