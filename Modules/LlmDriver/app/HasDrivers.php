<?php

namespace LlmLaraHub\LlmDriver;

interface HasDrivers
{
    public function getDriver(): string;

    public function getEmbeddingDriver(): string;

    public function getSummary(): string;

    public function getId(): int;

    public function getType(): string;
}
