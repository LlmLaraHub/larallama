<?php

namespace App\LlmDriver\Functions;

use Spatie\LaravelData\Attributes\WithCastable;

class ParametersDto extends \Spatie\LaravelData\Data
{

    /**
     * 
     * @param string $type 
     * @param ParameterDto[] $parameters 
     * @return void 
     */
    public function __construct(
        public string $type = "object",
        public array $parameters = [],
    ) {
    }
}
