<?php

namespace App\Domains\Generators;

use Illuminate\Support\Facades\File;

abstract class BaseRepository
{
    public string $name;

    public string $description;

    protected string $key;

    protected bool $requires_settings;

    protected string $class_name;

    public function setup(
        string $name,
        string $description,
        bool $requires_settings = false
    ) {
        $this->name = $name;
        $this->requires_settings = $requires_settings;
        $this->description = $description;
        $this->class_name = str($name)->studly()->toString();
        $this->key = str($name)->snake()->toString();

        return $this;
    }

    abstract public function run(): self;

    public function getKey(): string
    {
        return $this->key;
    }

    public function getRequiresSettings(): bool
    {
        return $this->requires_settings;
    }

    public function getClassName(): string
    {
        return $this->class_name;
    }

    public function putFile(string $pathWithName, string $content)
    {
        File::put($pathWithName, $content);
    }

    public function getRootPathOrStubs(): string
    {
        return base_path('STUBS/');
    }
}
