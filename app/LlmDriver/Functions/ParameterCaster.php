<?php

namespace App\LlmDriver\Responses;

use App\LlmDriver\Functions\ParameterDto;
use Pgvector\Laravel\Vector;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class ParamaterCaster implements Castable
{

    public static function dataCastUsing(...$arguments): Cast
    {
        return new class implements Cast
        {
            public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
            {
                return new ParameterDto($value);
            }
        };
    }
}
