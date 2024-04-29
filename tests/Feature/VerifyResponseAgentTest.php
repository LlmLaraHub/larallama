<?php

namespace Tests\Feature;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Agents\VerifyResponseAgent;
use App\Models\Chat;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class VerifyResponseAgentTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_agent(): void
    {
        $chat = Chat::factory()->create();

        $response = CompletionResponse::from([
            'content' => 'test',
        ]);

        LlmDriverFacade::shouldReceive('driver->completion')->once()->andReturn($response);

        $verifyPromptInput = VerifyPromptInputDto::from([
            'chattable' => $chat,
            'originalPrompt' => 'test',
            'context' => 'test',
            'llmResponse' => 'test',
            'verifyPrompt' => 'test',
        ]);

        $response = (new VerifyResponseAgent())->verify($verifyPromptInput);

        $this->assertInstanceOf(VerifyPromptOutputDto::class, $response);

    }
}
