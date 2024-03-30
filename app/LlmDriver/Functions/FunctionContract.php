<?php 

namespace App\LlmDriver\Functions;

abstract class FunctionContract
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function getFunction() : FunctionDto {
        return new FunctionDto($this->name, $this->getParameters());
    
    }


    abstract public function getParameters(): array;



    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    abstract public function handle(array $data): array;
}