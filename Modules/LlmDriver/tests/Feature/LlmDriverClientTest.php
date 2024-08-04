<?php

namespace Tests\Feature;

use LlmLaraHub\LlmDriver\Functions\ToolTypes;
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
        $functions = LlmDriverFacade::driver('mock')->getFunctions();

        $this->assertCount(9, $functions);

        $function = LlmDriverFacade::driver('mock')->setToolType(
            ToolTypes::ChatCompletion
        )->getFunctions();

        $this->assertCount(7, $function);

        $function = LlmDriverFacade::driver('mock')->setToolType(
            ToolTypes::Chat
        )->getFunctions();

        $this->assertCount(0, $function);
    }

    public function test_get_functions_for_ui()
    {
        $functions = LlmDriverFacade::getFunctionsForUi();

        $this->assertCount(8, $functions);

    }
}
