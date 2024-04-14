<?php

namespace LlmLaraHub\LlmDriver\Responses;

class CompletionResponse extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $content
    ) {
    }
}
