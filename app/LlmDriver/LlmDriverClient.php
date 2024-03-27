<?php

namespace App\LlmDriver;

class LlmDriverClient
{
    protected $drivers = [];

    public function driver($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    protected function createDriver($name)
    {
        switch ($name) {
            case 'openai':
                return new OpenAiClient();
            case 'mock':
                return new MockClient();
            default:
                throw new \InvalidArgumentException("Driver [{$name}] is not supported.");
        }
    }

    public static function getDrivers(): array
    {
        return array_keys(config('llmdriver.drivers'));
    }

    protected function getDefaultDriver()
    {
        return 'mock';
    }
}
