<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCastable;
use Spatie\LaravelData\Optional;

class ClaudeCompletionResponse extends CompletionResponse
{
    public function __construct(
        #[WithCastable(ClaudeContentCaster::class)]
        public mixed $content,
        public string|Optional $stop_reason,
        public ?string $tool_used = '',
        /** @var array<ToolDto> */
        #[WithCastable(ClaudeToolCaster::class)]
        #[MapInputName('content')]
        public array $tool_calls = [],
        #[MapInputName('usage.input_tokens')]
        public ?int $input_tokens = null,
        #[MapInputName('usage.output_tokens')]
        public ?int $output_tokens = null,
        public ?string $model = null,
    ) {
    }
}
