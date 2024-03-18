<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

class ExampleChatBotControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_chat(): void
    {
        $user = User::factory()->create();

        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                       'text' => "Mocked Reply"
                    ],
                ],
            ]),
        ]);

        $this->actingAs($user)
            ->put(route('example.chatbot.chat'), [
                'message' => "Foobar"
            ])
            ->assertStatus(200)->dd();

    }
}
