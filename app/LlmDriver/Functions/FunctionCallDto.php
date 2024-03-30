<?php

namespace App\LlmDriver\Functions;

use App\LlmDriver\ArgumentCaster;
use Spatie\LaravelData\Attributes\WithCastable;

class FunctionCallDto extends \Spatie\LaravelData\Data
{
    public function __construct(
        #[WithCastable(ArgumentCaster::class)]
        public array $arguments,
        public string $function_name,
    ) {
    }
}
