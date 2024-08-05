<?php

namespace Tests\Feature\Models;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
use App\Models\Output;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $collection = Collection::factory()->create();
        $model = Chat::factory()->create([
            'chatable_id' => $collection->id,
        ]);
        $this->assertNotNull($model->user_id);
        $this->assertNotNull($model->user->id);
        $this->assertNotNull($model->chatable_id);
        $this->assertNotNull($model->chatable->id);
        $this->assertEquals($collection->id, $model->chatable->id);
        $this->assertNotNull($model->chatable->systemPrompt());
        $this->assertNotNull($collection->chats()->first()->id);
    }

    public function test_system_message(): void
    {
        $collection = Collection::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
        ]);

        $dto = MetaDataDto::from([
            'persona' => 1,
            'filter' => 1,
            'completion' => false,
            'tool' => 'foobar',
            'date_range' => 'this_week',
            'input' => 'my input here',
        ]);

        $this->assertDatabaseCount('messages', 0);

        $chat->addInput(
            message: 'Test',
            role: RoleEnum::User,
            systemPrompt: 'Hello',
            meta_data: $dto
        );

        $this->assertDatabaseCount('messages', 2);

        $chat->addInput(
            message: 'Test',
            role: RoleEnum::User);

        $this->assertDatabaseCount('messages', 3);
    }

    public function test_first_or_create_using_output(): void
    {
        $output = Output::factory()->create();
        $chat = Chat::firstOrCreateUsingOutput($output);
        $this->assertNotNull($chat->session_id);
        $this->assertNotNull($chat->id);
    }

    public function test_get_chat_response()
    {
        $chat = Chat::factory()->create();
        $message = Message::factory()->create([
            'body' => 'This is a test',
            'role' => 'user',
            'chat_id' => $chat->id,
            'meta_data' => MetaDataDto::from([
                'tool_id' => 'test1',
                'tool' => 'test1',
                'args' => ['foo'],
            ]),
        ]);
        $messageTool = Message::factory()->create([
            'body' => 'This is a test',
            'role' => 'tool',
            'chat_id' => $chat->id,
            'meta_data' => MetaDataDto::from([
                'tool_id' => 'test2',
                'tool' => 'test2',
                'args' => [],
            ]),
        ]);

        $messages = $chat->getChatResponse();

        $message = $messages[0];

        $this->assertEquals($message->role, RoleEnum::User->value);
        $this->assertEquals($message->content, 'This is a test');
        $this->assertEquals($message->tool_id, 'test1');
        $this->assertEquals($message->tool, 'test1');
        $this->assertEquals($message->args, ['foo']);

    }
}
