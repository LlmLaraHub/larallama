<?php

namespace Tests\Feature;

use App\LlmDriver\Functions\FunctionDto;
use App\LlmDriver\Functions\ParameterDto;
use App\LlmDriver\Functions\ParametersDto;
use Tests\TestCase;

class FunctionDtoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {
        $dto = FunctionDto::from(
            [
                'name' => 'test',
                'description' => 'test',
                'parameters' => [
                    'type' => 'object',
                    'parameters' => [
                        [
                            'name' => 'test',
                            'description' => 'test',
                            'type' => 'string',
                            'enum' => [],
                            'default' => '',
                            'required' => false,
                        ],
                        [
                            'name' => 'test2',
                            'description' => 'test2',
                            'type' => 'string',
                            'enum' => ['foo', 'bar'],
                            'default' => 'bar',
                            'required' => true,
                        ],
                    ],
                ],
            ]
        );

        $this->assertNotNull($dto->name);
        $this->assertNotNull($dto->description);
        $this->assertInstanceOf(ParametersDto::class, $dto->parameters);
        $this->assertCount(2, $dto->parameters->parameters);
        $parameterOne = $dto->parameters->parameters[0];
        $this->assertInstanceOf(ParameterDto::class, $parameterOne);
        $this->assertEquals('test', $parameterOne->name);
        $this->assertEquals('test', $parameterOne->description);
        $this->assertEquals('string', $parameterOne->type);
        $this->assertEquals([], $parameterOne->enum);
        $this->assertEquals('', $parameterOne->default);
        $this->assertFalse($parameterOne->required);

        $parameterTwo = $dto->parameters->parameters[1];
        $this->assertInstanceOf(ParameterDto::class, $parameterTwo);
        $this->assertEquals('test2', $parameterTwo->name);
        $this->assertEquals('test2', $parameterTwo->description);
        $this->assertEquals('string', $parameterTwo->type);
        $this->assertEquals(['foo', 'bar'], $parameterTwo->enum);
        $this->assertEquals('bar', $parameterTwo->default);
        $this->assertTrue($parameterTwo->required);

    }
}
