<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Pgvector\Laravel\Vector;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class VectorCaster implements Castable
{
    public function __construct(public array $embedding)
    {

    }

    public static function dataCastUsing(...$arguments): Cast
    {
        return new class implements Cast
        {
            public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
            {
                return new Vector($value);
            }
        };
    }
}
