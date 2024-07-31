<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Spatie\LaravelData\Data;

class ToolDto extends Data
{

    public function __construct(
        public string $name,
        public array $arguments,
        public string $id = "",
    ) {
    }

}
