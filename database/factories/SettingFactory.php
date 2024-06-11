<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'steps' => [
                'setup_secrets' => false,
            ],
            'meta_data' => [
                'openai' => [
                    'models' => [
                        'completion_model' => 'gpt-3.5-turbo',
                    ],
                ],
            ],
            'secrets' => [
                'openai' => [
                    'api_key' => 'foobar',
                ],
            ],
            'user_id' => User::factory(),
        ];
    }
}
