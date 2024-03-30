<?php

namespace App\LlmDriver\Functions;

abstract class FunctionContract
{
    protected string $name;
    
    protected string $dscription;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    abstract public function handle(array $data): array;

    public function getFunction(): FunctionDto
    {
        return FunctionDto::from(
            [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => $this->getParameters(),
            ]
        );

    }

    protected function getName(): string
    {
        return $this->name;
    }


    protected function getParameters(): array {
        return [];
    }


    protected function getDescription(): string
    {
        return $this->name;
    }
}
