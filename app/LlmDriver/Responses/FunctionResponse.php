<?php

namespace App\LlmDriver\Responses;

/**
 * @NOTE
 * Requires follow up with be for example results of a panda query on a csv file
 * maybe more info is needed from an llm or agent
 */
class FunctionResponse extends \Spatie\LaravelData\Data
{
    public function __construct(
        public string $content,
        public bool $requires_follow_up_prompt = false
    ) {
    }
}
