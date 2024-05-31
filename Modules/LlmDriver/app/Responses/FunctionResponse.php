<?php

namespace LlmLaraHub\LlmDriver\Responses;

use Illuminate\Support\Collection;

/**
 * @NOTE
 * Requires follow up with be for example results of a panda query on a csv file
 * maybe more info is needed from an llm or agent
 */
class FunctionResponse extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $content,
        public string $prompt = '',
        public bool $requires_follow_up_prompt = false,
        public bool $save_to_message = true,
        public ?Collection $documentChunks = null,
        public ?Collection $documents = null,
    ) {
    }
}
