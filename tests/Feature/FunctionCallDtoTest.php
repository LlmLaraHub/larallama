<?php

namespace Tests\Feature;

use App\Models\Filter;
use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;
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

    public function test_dto_filter(): void
    {
        $filter = Filter::factory()->create();

        $dto = FunctionCallDto::from([
            'arguments' => json_encode(['TLDR it for me']),
            'function_name' => 'summarize_collection',
            'filter' => $filter,
        ]);

        $this->assertInstanceOf(Filter::class, $dto->filter);
    }
}
