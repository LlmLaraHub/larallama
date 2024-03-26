<?php

namespace Tests\Feature;

use App\LlmDriver\LlmDriverFacade;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LlmDriverFacadeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_facade(): void
    {
        $results = LlmDriverFacade::embedData("test");

        $this->assertInstanceOf(
            EmbeddingsResponseDto::class,
            $results
        );
    }
}
