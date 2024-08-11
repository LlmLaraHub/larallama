<?php

namespace LlmLaraHub\LlmDriver;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasDriversTrait
{
    public function documents(): HasMany
    {
        return $this->getChatable()->documents();
    }

    public function systemPrompt(): string
    {
        return $this->getChatable()->systemPrompt();
    }
}
