<?php

namespace App\LlmDriver\Functions;

use App\LlmDriver\Functions\PropertyDto;

abstract class FunctionContract
{
    protected string $name;

    protected string $description;

    protected string $type = 'object';

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

    protected function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return PropertyDto[]
     */
    abstract protected function getProperties(): array;
}
