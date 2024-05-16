<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class NonFunctionResponseDto extends Data
{
    public function __construct(
        public Collection $documentChunks,
        public string $response = '',
        public string $prompt = '',
    ) {

    }
}
