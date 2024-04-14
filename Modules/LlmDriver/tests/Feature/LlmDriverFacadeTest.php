<?php

namespace Tests\Feature;

use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use Tests\TestCase;

class LlmDriverFacadeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_facade(): void
    {
        $results = LlmDriverFacade::driver('mock')->embedData('test');

        $this->assertInstanceOf(
            EmbeddingsResponseDto::class,
            $results
        );
    }
}
