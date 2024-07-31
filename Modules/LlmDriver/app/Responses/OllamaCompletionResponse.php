<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Spatie\LaravelData\Attributes\MapInputName;

class OllamaCompletionResponse extends CompletionResponse
{
    public function __construct(
        #[MapInputName('message.content')]
        public string $content,
        #[MapInputName('done_reason')]
        public string $stop_reason = 'end_turn',
        public ?string $tool_used = null,
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
