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
            'tool_id' => 'foobaz',
            'driver' => 'mock',
            'args' => [
                'foo' => 'bar',
            ],
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

        $message = Message::whereRole(RoleEnum::User)->first();
        $this->assertEquals('foobar', $message->meta_data->tool);
        $this->assertEquals('foobaz', $message->meta_data->tool_id);
        $this->assertEquals('mock', $message->meta_data->driver);
        $this->assertEquals(['foo' => 'bar'], $message->meta_data->args);

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

    public function test_add_input_tools_and_args(): void
    {
        $chat = Chat::factory()->create();
        $message = $chat->addInput(
            message: 'Foo bar',
            role: RoleEnum::User,
            systemPrompt: 'Foo bar',
            show_in_thread: true,
            meta_data: MetaDataDto::from(
                [
                    'tool' => 'standards_checker',
                    'tool_id' => 'foobar',
                    'args' => ['foo' => 'bar'],
                ]
            )
        );
        $this->assertEquals('Foo bar', $message->body);
        $this->assertEquals('foobar', $message->tool_id);
        $this->assertEquals('standards_checker', $message->tool_name);
        $this->assertEquals(['foo' => 'bar'], $message->args);
    }

    public function test_get_chat_response(): void
    {
        $chat = Chat::factory()->create();
        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'body' => 'Foo bar',
            'tool_name' => 'standards_checker',
            'tool_id' => 'foobar',
            'args' => ['foo' => 'bar'],
        ]);
        $response = $chat->getChatResponse();
        $message1 = $response[0];
        $this->assertEquals('Foo bar', $message1->content);
        $this->assertEquals('foobar', $message1->tool_id);
        $this->assertEquals('standards_checker', $message1->tool);
        $this->assertEquals(['foo' => 'bar'], $message1->args);
    }
}
