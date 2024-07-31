<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use LlmLaraHub\LlmDriver\Responses\OllamaCompletionResponse;
use LlmLaraHub\LlmDriver\Responses\OllamaToolDto;
use Tests\TestCase;

class OllamaCompletionResponseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {
        $results = get_fixture('ollama_response_tools.json');
        $dto = OllamaCompletionResponse::from($results);
        $this->assertNotNull($dto->stop_reason);
        $this->assertNotNull($dto->model);
        $this->assertNotNull($dto->content);
        $this->assertNotNull($dto->tool_calls);
        $this->assertNotNull($dto->input_tokens);
        $this->assertNotNull($dto->output_tokens);
        $tool = Arr::first($dto->tool_calls);
        $this->assertInstanceOf(OllamaToolDto::class, $tool);
    }
}
