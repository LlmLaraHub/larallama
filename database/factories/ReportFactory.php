<?php

namespace Database\Factories;

use App\Domains\Reporting\StatusEnum;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
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
            'user_message_id' => Message::factory(),
            'reference_collection_id' => Collection::factory(),
            'message_id' => null,
            'type' => \App\Domains\Reporting\ReportTypeEnum::RFP,
            'status_sections_generation' => StatusEnum::Pending,
            'status_entries_generation' => StatusEnum::Pending,
        ];
    }
}
