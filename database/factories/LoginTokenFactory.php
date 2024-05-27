<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoginToken>
 */
class LoginTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'token' => Uuid::uuid4()->toString(),
            'expires_at' => now()->addMinute(30),
            'user_id' => User::factory(),
        ];
    }
}
