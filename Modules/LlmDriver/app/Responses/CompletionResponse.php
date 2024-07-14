<?php

namespace LlmLaraHub\LlmDriver\Responses;

class CompletionResponse extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $content,
        public string $stop_reason = 'end_turn',
        public ?string $tool_used = null,
        public ?int $input_tokens = null,
        public ?int $output_tokens = null,
    ) {
    }
}
