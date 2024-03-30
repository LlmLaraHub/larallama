<?php

namespace App\LlmDriver\Functions;

class FunctionDto extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $name,
        public string $description,
        public ParametersDto $parameters,
    ) {
    }
}
