<?php

namespace App\LlmDriver\Responses;

class FunctionResponse extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $content
    ) {
    }
}
