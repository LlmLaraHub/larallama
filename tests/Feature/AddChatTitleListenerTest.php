<?php

namespace Tests\Feature;

use App\Domains\Messages\RoleEnum;
use App\Events\MessageCreatedEvent;
use App\Listeners\AddChatTitleListener;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AddChatTitleListenerTest extends TestCase
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

        $event = new MessageCreatedEvent($message);

        $listener = new AddChatTitleListener();

        $listener->handle($event);

        $this->assertEquals('Test Message',
            $chat->refresh()->title);

    }
}
