<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Chat;
use App\Models\Collection;
use App\Models\User;
use Facades\LlmLaraHub\LlmDriver\Orchestrate;
use Facades\LlmLaraHub\LlmDriver\SimpleSearchAndSummarizeOrchestrate;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
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
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        LlmDriverFacade::shouldReceive('driver->hasFunctions')->once()->andReturn(false);
        SimpleSearchAndSummarizeOrchestrate::shouldReceive('handle')->once()->andReturn('Yo');

        $this->actingAs($user)->post(route('chats.messages.create', [
            'chat' => $chat->id,
        ]),
            [
                'system_prompt' => 'Foo',
                'input' => 'user input',
            ])->assertOk();

    }
}
