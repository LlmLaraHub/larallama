<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
use App\Models\User;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Facades\LlmLaraHub\LlmDriver\Orchestrate;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_create_chat_and_redirect(): void
    {
        $user = User::factory()->create();

        $collection = Collection::factory()->create();
        $this->assertDatabaseCount('chats', 0);
        $this->actingAs($user)->post(route('chats.collection.store', [
            'collection' => $collection->id,
        ]))->assertRedirect();
        $this->assertDatabaseCount('chats', 1);
    }

    public function test_will_verify_on_completion(): void
    {
        $this->markTestSkipped('@TODO will come back to validation soon moving it to a function');

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        Orchestrate::shouldReceive('handle')->never();

        $firstResponse = CompletionResponse::from([
            'content' => 'test',
        ]);

        LlmDriverFacade::shouldReceive('driver->chat')->once()->andReturn($firstResponse);

        VerifyResponseAgent::shouldReceive('verify')->never()->andReturn(
            VerifyPromptOutputDto::from(
                [
                    'chattable' => $chat,
                    'originalPrompt' => 'test',
                    'context' => 'test',
                    'llmResponse' => 'test',
                    'verifyPrompt' => 'This is a completion so the users prompt was past directly to the llm with all the context.',
                    'response' => 'verified yay!',
                ]
            ));

        $this->assertDatabaseCount('messages', 0);
        $this->actingAs($user)->post(route('chats.messages.create', [
            'chat' => $chat->id,
        ]),
            [
                'system_prompt' => 'Foo',
                'input' => 'user input',
                'completion' => true,
            ])->assertOk();
        $this->assertDatabaseCount('messages', 2);
        $message = Message::where('role', RoleEnum::Assistant)->first();

        $this->assertEquals('test', $message->body);
    }

    public function test_a_function_based_chat()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        Orchestrate::shouldReceive('handle')->once()->andReturn('Yo');

        $this->assertDatabaseCount('messages', 0);
        $this->actingAs($user)->post(route('chats.messages.create', [
            'chat' => $chat->id,
        ]),
            [
                'system_prompt' => 'Foo',
                'input' => 'user input',
            ])->assertOk();
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_kick_off_chat_makes_system()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('messages', 0);
        $this->actingAs($user)->post(route('chats.messages.create', [
            'chat' => $chat->id,
        ]),
            [
                'system_prompt' => 'Foo',
                'input' => 'user input',
            ])->assertOk();
        $this->assertDatabaseCount('messages', 2);

    }

    public function test_no_functions()
    {
        Bus::fake();
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        LlmDriverFacade::shouldReceive('driver->hasFunctions')->once()->andReturn(false);

        $this->actingAs($user)->post(route('chats.messages.create', [
            'chat' => $chat->id,
        ]),
            [
                'system_prompt' => 'Foo',
                'input' => 'user input',
            ])->assertOk();

        Bus::assertBatchCount(1);

    }

    public function test_standard_checker()
    {
        Orchestrate::shouldReceive('handle')->once()->andReturn('Yo');

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post(route('chats.messages.create', [
            'chat' => $chat->id,
        ]),
            [
                'system_prompt' => 'Foo',
                'input' => 'user input',
                'tool' => 'standards_checker',
            ])->assertOk();

    }
}
