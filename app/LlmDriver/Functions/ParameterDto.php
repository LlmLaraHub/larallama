<?php

namespace App\LlmDriver\Functions;

use Spatie\LaravelData\Attributes\WithCastable;

class ParameterDto extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $name,
        public string $description,
        public string $type = "string",
        public array $enum = [],
        public string $default = "",
        public bool $required = false,
    ) {
    }
}
