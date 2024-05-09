<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromptHistory>
 */
class PromptHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prompt' => $this->faker->text(),
            'chat_id' => Chat::factory(),
            'collection_id' => Collection::factory(),
            'message_id' => Message::factory(),
        ];
    }
}
