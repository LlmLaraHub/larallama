<?php

namespace LlmLaraHub\LlmDriver\Functions;

class PropertyDto extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $name,
        public string $description,
        public string $type = 'string',
        public array $enum = [],
        public array $properties = [],
        public string $default = '',
        public bool $required = false,
    ) {
    }
}
