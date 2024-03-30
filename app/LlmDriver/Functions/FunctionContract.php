<?php

namespace App\LlmDriver\Functions;

abstract class FunctionContract
{
    protected string $name;

    protected string $dscription;

    protected string $type = 'object';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    abstract public function handle(FunctionCallDto $functionCallDto): array;

    public function getFunction(): FunctionDto
    {
        return FunctionDto::from(
            [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => [
                    'type' => $this->type,
                    'properties' => $this->getProperties(),
                ],
            ]
        );
    }

    protected function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ParameterDto[]
     */
    abstract protected function getProperties(): array;

    protected function getDescription(): string
    {
        return $this->name;
    }
}
