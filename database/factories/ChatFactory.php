<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use LlmLaraHub\LlmDriver\DriversEnum;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => 'Test Title',
            'chatable_id' => Collection::factory(),
            'chatable_type' => Collection::class,
        ];
    }

    public function withDrivers(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'chat_driver' => DriversEnum::Claude->value,
                'embedding_driver' => DriversEnum::Claude->value,
            ];
        });
    }
}
