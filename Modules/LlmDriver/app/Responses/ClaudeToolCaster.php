<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class ClaudeToolCaster implements Castable
{
    public function __construct(public array $tools)
    {

    }

    public static function dataCastUsing(...$arguments): Cast
    {
        return new class implements Cast
        {
            public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
            {
                $results = collect($value)->filter(
                    function ($item) {
                        return $item['type'] === 'tool_use';
                    }
                )->toArray();

                foreach ($results as $index => $result) {
                    $results[$index] = ToolDto::from(
                        [
                            'name' => $result['name'],
                            'arguments' => $result['input'],
                            'id' => $result['id'],
                        ]
                    );
                }

                return $results;
            }
        };
    }
}
