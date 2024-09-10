<?php

namespace Tests\Feature;

use App\Domains\Projects\Orchestrate;
use App\Models\Chat;
use App\Models\Project;
use LlmLaraHub\LlmDriver\DriversEnum;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class ProjectOrchestrateTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_tools(): void
    {
        $response = get_fixture('claude_chat_response.json', false);

        $project = Project::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_id' => $project->id,
            'chatable_type' => Project::class,
            'chat_driver' => DriversEnum::Claude,
            'embedding_driver' => DriversEnum::Ollama,
        ]);

        $this->assertDatabaseCount('messages', 0);
        $this->assertDatabaseCount('tasks', 0);

        LlmDriverFacade::shouldReceive('driver->setSystemPrompt->setToolType->chat')
            ->once()
            ->andReturn(
                CompletionResponse::from($response)
            );

        LlmDriverFacade::shouldReceive('driver->setToolType->setSystemPrompt->chat')
            ->once()
            ->andReturn(
                CompletionResponse::from($response)
            );

        (new Orchestrate())->handle($chat, 'Test Prompt', 'System Prompt');

        $this->assertDatabaseCount('messages', 4);
        $this->assertDatabaseCount('tasks', 5);
    }
}
