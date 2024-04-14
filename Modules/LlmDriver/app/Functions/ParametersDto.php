<?php

namespace LlmLaraHub\LlmDriver\Functions;

class ParametersDto extends \Spatie\LaravelData\Data
{
    /**
     * @param  PropertyDto[]  $properties
     * @return void
     */
    public function __construct(
        public string $type = 'object',
        public array $properties = [],
    ) {
    }
}
