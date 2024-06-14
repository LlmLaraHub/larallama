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
                'groq' => [
                    'api_key' => 'foobar',
                ],
            ],
            'user_id' => User::factory(),
        ];
    }

    public function all_have_keys(): Factory
    {
        return $this->state(function (array $attributes) {
            $attributes['secrets']['groq'] = [
                'api_key' => 'foobar',
            ];
            $attributes['secrets']['openai'] = [
                'api_key' => 'foobar',
                'api_url' => 'https://api.openai.com/v1',
            ];
            $attributes['secrets']['ollama'] = [
                'api_key' => 'foobar',
                'api_url' => 'https://api.anthropic.com/v1',
            ];
            $attributes['secrets']['claude'] = [
                'api_key' => 'foobar',
                'api_url' => 'http://localhost:11434/api/',
            ];

            return $attributes;
        });
    }
}
