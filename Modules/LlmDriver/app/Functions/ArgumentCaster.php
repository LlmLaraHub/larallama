<?php

namespace LlmLaraHub\LlmDriver\Functions;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class ArgumentCaster implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): array
    {

        if (is_array($value)) {
            return $value;
        }

        return json_decode($value, true);
    }
}
