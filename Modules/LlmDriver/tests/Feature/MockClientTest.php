<?php

namespace Tests\Feature;

use LlmLaraHub\LlmDriver\MockClient;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use Tests\TestCase;

class MockClientTest extends TestCase
{
    public function test_tools(): void
    {

        $client = new MockClient();

        $results = $client->functionPromptChat(['test']);

        $this->assertCount(1, $results);

        $this->assertEquals('summarize_collection', $results[0]['name']);
    }

    public function test_tool_with_limit(): void
    {
        $client = new MockClient();

        $results = $client->functionPromptChat(['test'], ['search_and_summarize']);

        $this->assertCount(1, $results);
    }

    public function test_embeddings(): void
    {

        $client = new MockClient();

        $results = $client->embedData('test');

        $this->assertInstanceOf(EmbeddingsResponseDto::class, $results);

    }

    public function test_completion(): void
    {
        $client = new MockClient();

        $results = $client->completion('test');

        $this->assertInstanceOf(CompletionResponse::class, $results);

    }

    public function test_Chat(): void
    {
        $client = new MockClient();

        $results = $client->chat([
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
        ]);

        $this->assertInstanceOf(CompletionResponse::class, $results);

    }

    public function test_max_token(): void
    {
        $client = new MockClient();

        $results = $client->getMaxTokenSize('claude');

        $this->assertEquals(4096, $results);
    }
}
