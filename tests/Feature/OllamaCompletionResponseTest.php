<?php

namespace Tests\Feature;

use LlmLaraHub\LlmDriver\Responses\OllamaCompletionResponse;
use Tests\TestCase;

class OllamaCompletionResponseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {
        $results = get_fixture('ollama_completion_v2.json');
        $dto = OllamaCompletionResponse::from($results);
        $this->assertNotNull($dto->stop_reason);
        $this->assertNotNull($dto->model);
        $this->assertNotNull($dto->content);
        $this->assertNotNull($dto->tool_calls);
        $this->assertNotNull($dto->input_tokens);
        $this->assertNotNull($dto->output_tokens);
    }
}
