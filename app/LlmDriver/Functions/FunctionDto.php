<?php

namespace App\LlmDriver\Functions;

use Spatie\LaravelData\Attributes\WithCastable;

class FunctionDto extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $name,
        public string $description,
        public ParametersDto $parameters,
    ) {
    }
}
