<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ToolDto extends Data
{
    public function __construct(
        public string $name,
        public array $arguments,
        public string|Optional $id = '',
    ) {
    }
}
