<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Filter;
use Spatie\LaravelData\Attributes\WithCast;

class FunctionCallDto extends \Spatie\LaravelData\Data
{
    public function __construct(
        #[WithCast(ArgumentCaster::class)]
        public array $arguments,
        public string $function_name,
        public ?Filter $filter = null
    ) {
    }
}
