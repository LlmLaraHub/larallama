<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
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
            'chat_id' => Chat::factory(),
            'reference_collection_id' => Collection::factory(),
            'type' => \App\Domains\Reporting\ReportTypeEnum::RFP,

        ];
    }
}
