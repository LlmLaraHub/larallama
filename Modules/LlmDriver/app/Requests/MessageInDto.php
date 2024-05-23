<?php

namespace LlmLaraHub\LlmDriver\Requests;

use Spatie\LaravelData\Data;

class MessageInDto extends Data
{
    public function __construct(
        public string $content,
        public string $role,
        public bool $is_ai = false,
        public bool $show = true
    ) {
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'role' => $this->role,
        ];
    }
}
