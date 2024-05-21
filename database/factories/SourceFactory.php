<?php

namespace Database\Factories;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Source>
 */
class SourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name,
            'collection_id' => Collection::factory(),
            'details' => $this->faker->sentence, // 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            'recurring' => RecurringTypeEnum::Daily,
            'active' => true,
            'type' => $this->faker->randomElement(SourceTypeEnum::values()),
            'meta_data' => [
                'driver' => 'brave',
                'limit' => 5,
                'api_key' => $this->faker->uuid,
            ],
        ];
    }
}
