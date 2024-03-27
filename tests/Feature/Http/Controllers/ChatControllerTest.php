<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\User;
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
}
