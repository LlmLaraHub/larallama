<?php

namespace Tests\Feature;

use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\Models\Message;
use Facades\App\Domains\Chat\TitleRepo;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TitleRepoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_makes_title(): void
    {
        Event::fake();

        $chat = Chat::factory()->create([
            'title' => null,
        ]);

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'body' => 'Test Message',
            'role' => RoleEnum::User,
        ]);

        TitleRepo::handle($message);

        $this->assertEquals('Test Message',
            $chat->refresh()->title);

    }

    public function test_updates_all_titles(): void
    {
        Event::fake();

        $chat = Chat::factory()->create([
            'title' => null,
        ]);

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'body' => 'Test Message',
            'role' => RoleEnum::User,
        ]);

        $message2 = Message::factory()->create([
            'chat_id' => $chat->id,
            'body' => 'Test Message 2',
            'role' => RoleEnum::User,
        ]);

        TitleRepo::updateAllTitles();

        $this->assertEquals('Test Message',
            $chat->refresh()->title);

    }
}
