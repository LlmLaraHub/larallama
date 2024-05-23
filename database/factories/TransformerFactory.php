<?php

namespace Database\Factories;

use App\Domains\Transformers\TypeEnum;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transformer>
 */
class TransformerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => TypeEnum::GenericTransformer,
            'details' => fake()->sentences(3, true),
            'transformable_id' => Source::factory(),
            'transformable_type' => Source::class,
            'parent_id' => null,
            'last_run' => now(),
            'active' => true,
        ];
    }
}
