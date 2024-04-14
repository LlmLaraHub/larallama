<?php

namespace LlmLaraHub\LlmDriver;

interface HasDrivers
{
    public function getDriver(): string;

    public function getEmbeddingDriver(): string;
}
