<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Spatie\LaravelData\Optional;

class CompletionResponse extends \Spatie\LaravelData\Data
{
    public function __construct(
        public mixed $content,
        public string|Optional $stop_reason,
        public ?string $tool_used = '',
        /** @var array<ToolDto> */
        public array $tool_calls = [],
        public ?int $input_tokens = null,
        public ?int $output_tokens = null,
        public ?string $model = null,
    ) {
    }
}
