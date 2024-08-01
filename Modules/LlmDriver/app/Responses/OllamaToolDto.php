<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Optional;

class OllamaToolDto extends ToolDto
{
    public function __construct(
        #[MapInputName('function.name')]
        public string $name,
        #[MapInputName('function.arguments')]
        public array $arguments,
        public string|Optional $id = '',
    ) {
    }
}
