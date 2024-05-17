<?php

namespace LlmLaraHub\LlmDriver;

use App\Models\Chat;

interface HasDrivers
{
    public function getDriver(): string;

    public function getEmbeddingDriver(): string;

    public function getSummary(): string;

    public function getId(): int;

    public function getType(): string;

    public function getChatable(): HasDrivers;

    public function getChat(): ?Chat;
}
