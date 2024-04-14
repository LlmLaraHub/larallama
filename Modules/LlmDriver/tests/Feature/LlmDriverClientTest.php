<?php

namespace Tests\Feature;

use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\MockClient;
use LlmLaraHub\LlmDriver\OpenAiClient;
use Tests\TestCase;

class LlmDriverClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_driver(): void
    {
        $results = LlmDriverFacade::driver('mock');

        $this->assertInstanceOf(MockClient::class, $results);
    }

    public function test_driver_openai(): void
    {
        $results = LlmDriverFacade::driver('openai');

        $this->assertInstanceOf(OpenAiClient::class, $results);
    }

    public function test_get_functions()
    {
        $this->assertNotEmpty(LlmDriverFacade::getFunctions());
    }
}
