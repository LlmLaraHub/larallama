<?php

namespace Database\Factories;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Chat\ToolsDto;
use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'body' => $this->faker->paragraphs(3, true),
            'in_out' => $this->faker->boolean,
            'role' => RoleEnum::User,
            'chat_id' => Chat::factory(),
            'meta_data' => MetaDataDto::from([
                'persona' => 1,
                'filter' => 1,
                'completion' => false,
                'tool' => 'foobar',
                'date_range' => 'this_week',
                'input' => 'my input here',
            ]),
            'tool_name' => 'standards_checker',
            'tool_id' => 'foobar',
            'user_id' => User::factory(),
            'driver' => 'mock',
            'args' => ['url' => 'https://www.larallama.io'],
            'tools' => ToolsDto::from([
                'tools' => [
                    FunctionCallDto::from([
                        'arguments' => '{}',
                        'function_name' => 'standards_checker',
                    ]),
                ],
            ]),
        ];
    }

    public function assistant(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => RoleEnum::Assistant,
            ];
        });
    }

    public function user(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => RoleEnum::User,
            ];
        });
    }
}
