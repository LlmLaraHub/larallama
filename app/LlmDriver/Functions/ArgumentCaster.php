<?php

namespace App\LlmDriver\Functions;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class ArgumentCaster implements Cast
{

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): array
    {
        return json_decode($value, true);
    }

}
