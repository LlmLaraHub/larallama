<?php

namespace Tests\Feature;

use App\LlmDriver\Functions\ParameterDto;
use App\LlmDriver\Functions\ParametersDto;
use App\LlmDriver\Functions\PropertyDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchAndSummarizeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_generate_function_as_array(): void
    {
        $searchAndSummarize = new \App\LlmDriver\Functions\SearchAndSummarize();

        $function = $searchAndSummarize->getFunction();

        $parameters = $function->parameters;

        $this->assertInstanceOf(ParametersDto::class, $parameters);
        $this->assertIsArray($parameters->properties);
        $this->assertInstanceOf(PropertyDto::class, $parameters->properties[0]);
    }
}
