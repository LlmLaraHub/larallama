<?php

namespace LlmLaraHub\LlmDriver;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasDrivers
{
    public function getDriver(): string;

    public function getEmbeddingDriver(): string;

    public function getSummary(): string;

    public function getId(): int;

    public function getType(): string;

    public function getChatable(): HasDrivers;

    public function documents(): HasMany;

    public function getChat(): ?Chat;
}
