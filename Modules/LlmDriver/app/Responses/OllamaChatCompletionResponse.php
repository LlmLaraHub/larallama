<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Optional;

class OllamaChatCompletionResponse extends CompletionResponse
{
    public function __construct(
        #[MapInputName('message.content')]
        public mixed $content,
        #[MapInputName('done_reason')]
        public string|Optional $stop_reason,
        public ?string $tool_used = '',
        /** @var array<OllamaToolDto> */
        #[MapInputName('message.tool_calls')]
        public array $tool_calls = [],
        #[MapInputName('prompt_eval_count')]
        public ?int $input_tokens = null,
        #[MapInputName('eval_count')]
        public ?int $output_tokens = null,
        public ?string $model = null,
    ) {
    }
}
