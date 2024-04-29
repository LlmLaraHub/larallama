<?php

namespace App\Domains\Agents;

use LlmLaraHub\LlmDriver\HasDrivers;
use Spatie\LaravelData\Data;

class VerifyPromptInputDto extends Data
{
    public function __construct(
        public HasDrivers $chattable,
        public string $originalPrompt,
        public string $context,
        public string $llmResponse,
        public string $verifyPrompt
    ) {
    }
}
