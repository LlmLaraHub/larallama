<?php

namespace LlmLaraHub\LlmDriver\Requests;

use Spatie\LaravelData\Data;

class MessageInDto extends Data
{
    public function __construct(
        public string $content,
        public string $role,
    ) {
    }
}
