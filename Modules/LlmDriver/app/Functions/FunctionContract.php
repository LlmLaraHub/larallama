<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Message;
use Illuminate\Bus\Batch;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

abstract class FunctionContract
{
    protected string $name;

    public bool $showInUi = true;

    public array $toolTypes = [
        ToolTypes::Chat,
        ToolTypes::ChatCompletion,
        ToolTypes::Source,
        ToolTypes::Output,
        ToolTypes::NoFunction,
    ];

    protected string $description;

    protected string $type = 'object';

    public Batch $batch;

    public function setBatch(Batch $batch): self
    {
        $this->batch = $batch;

        return $this;
    }

    abstract public function handle(
        Message $message,
    ): FunctionResponse;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function getKey(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function runAsBatch(): bool
    {
        return false;
    }

    public function getParameters(): array
    {
        return $this->getProperties();
    }

    /**
     * @return PropertyDto[]
     */
    abstract protected function getProperties(): array;
}
