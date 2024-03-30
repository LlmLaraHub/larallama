<?php

namespace App\LlmDriver\Functions;

class ParametersDto extends \Spatie\LaravelData\Data
{
    /**
     * @param  ParameterDto[]  $parameters
     * @return void
     */
    public function __construct(
        public string $type = 'object',
        public array $parameters = [],
    ) {
    }
}
