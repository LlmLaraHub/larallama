<?php

namespace App\LlmDriver\Responses;

class EmbeddingsResponseDto extends \Spatie\LaravelData\Data
{
    public function __construct(
        public array $embedding,
        public int $token_count
    ) {
    }
}
