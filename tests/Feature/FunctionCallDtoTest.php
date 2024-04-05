<?php

namespace Tests\Feature;

use App\LlmDriver\Functions\FunctionCallDto;
use Tests\TestCase;

class FunctionCallDtoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {
        $dto = FunctionCallDto::from([
            'arguments' => json_encode(['TLDR it for me']),
            'function_name' => 'summarize_collection',
        ]);

        $this->assertIsArray($dto->arguments);
        $this->assertNotEmpty($dto->arguments);
    }
}
