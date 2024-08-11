<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
use Facades\App\Domains\Orchestration\OrchestrateVersionTwo;
use LlmLaraHub\LlmDriver\Functions\GetWebSiteFromUrlTool;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class OrchestrateVersionTwoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_source_orchestrate(): void
    {

        $chat = Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => Collection::factory()->create()->id,
        ]);

        $prompt = 'Test prompt';

        $this->instance(
            'get_web_site_from_url',
            Mockery::mock(GetWebSiteFromUrlTool::class,
                function (MockInterface $mock) {
                    $mock->shouldReceive('handle')
                        ->once()
                        ->andReturn(
                            FunctionResponse::from([
                                'content' => 'Test',
                                'prompt' => 'test',
                                'requires_followup' => false,
                                'documentChunks' => collect([]),
                                'save_to_message' => false,
                            ])
                        );
                })
        );
        LlmDriverFacade::shouldReceive('driver->setToolType->chat')->twice()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
                'tool_calls' => [
                    'tool_calls' => [
                        'name' => 'get_web_site_from_url',
                        'arguments' => ['prompt' => 'Test prompt'],
                    ],
                ],
            ])
        );

        $results = OrchestrateVersionTwo::sourceOrchestrate($chat, $prompt);

        $this->assertInstanceOf(Message::class, $results);
    }
}
