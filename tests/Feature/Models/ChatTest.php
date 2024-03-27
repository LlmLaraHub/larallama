<?php

namespace Tests\Feature\Models;

use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\Models\Collection;
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
        $this->assertNotNull($model->chatable->systemPrompt());
        $this->assertNotNull($collection->chats()->first()->id);
    }

    public function test_system_message(): void
    {
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
        ]);

        $this->assertDatabaseCount('messages', 0);
        $chat->addInput(
            message: 'Test',
            role: RoleEnum::User,
            systemPrompt: 'Hello'
        );
        $this->assertDatabaseCount('messages', 2);
        $chat->addInput(
            message: 'Test',
            role: RoleEnum::User);
        $this->assertDatabaseCount('messages', 3);
    }
}
