<?php

namespace LlmLaraHub\LlmDriver\Tests\Feature;

use Illuminate\Support\Arr;
use LlmLaraHub\LlmDriver\Responses\ClaudeCompletionResponse;
use LlmLaraHub\LlmDriver\Responses\ToolDto;
use Tests\TestCase;

class ClaudeCompletionResponseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {
        $results = get_fixture('cloud_client_tool_use_response.json');
        $dto = ClaudeCompletionResponse::from($results);
        $this->assertNotNull($dto->stop_reason);
        $this->assertNotNull($dto->model);
        $this->assertNotNull($dto->content);
        $this->assertNotNull($dto->tool_calls);
        $tool = Arr::first($dto->tool_calls);
        $this->assertInstanceOf(ToolDto::class, $tool);
    }
}
