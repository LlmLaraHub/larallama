<?php

namespace Tests\Feature;

use App\Domains\Llms\DriverContract;
use App\Domains\Llms\LlmWrapper;
use Tests\TestCase;

class LlmWrapperTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_builds_open_ai_wrapper(): void
    {
        $llm = LlmWrapper::make();

        $this->assertInstanceOf(DriverContract::class, $llm);
    }
}
