<?php

namespace Database\Factories;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Output>
 */
class OutputFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title,
            'collection_id' => Collection::factory(),
            'last_run' => now(),
            'active' => fake()->boolean,
            'recurring' => RecurringTypeEnum::Daily,
            'type' => OutputTypeEnum::WebPage,
            'meta_data' => [],
            'public' => fake()->boolean,
            'summary' => fake()->sentences(4, true),
        ];
    }
}
