<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use LlmLaraHub\LlmDriver\Responses\OllamaChatCompletionResponse;
use LlmLaraHub\LlmDriver\Responses\OllamaToolDto;
use Tests\TestCase;

class OllamaChatCompletionResponseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {
        $results = get_fixture('ollama_response_tools.json');
        $dto = OllamaChatCompletionResponse::from($results);
        $this->assertNotNull($dto->stop_reason);
        $this->assertNotNull($dto->model);
        $this->assertNotNull($dto->content);
        $this->assertNotNull($dto->tool_calls);
        $this->assertNotNull($dto->input_tokens);
        $this->assertNotNull($dto->output_tokens);
        $tool = Arr::first($dto->tool_calls);
        foreach ($dto->tool_calls as $tool) {
            $this->assertInstanceOf(OllamaToolDto::class, $tool);
        }
        $this->assertInstanceOf(OllamaToolDto::class, $tool);
    }
}
