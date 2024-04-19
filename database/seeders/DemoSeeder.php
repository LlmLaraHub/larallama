<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ApiKey;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        ApiKey::factory(5)->create();

        Chat::factory(20)->create()->each(function ($chat) {
            $chat->messages()->saveMany(Message::factory(30)->make());
        });
    }
}
