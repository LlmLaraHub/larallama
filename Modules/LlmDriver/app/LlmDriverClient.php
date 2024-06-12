<?php

namespace LlmLaraHub\LlmDriver;

use LlmLaraHub\LlmDriver\Functions\SearchAndSummarize;
use LlmLaraHub\LlmDriver\Functions\StandardsChecker;
use LlmLaraHub\LlmDriver\Functions\SummarizeCollection;

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
        /**
         * @TODO
         * Turn into a match statement
         */
        switch ($name) {
            case 'openai':
                return new OpenAiClient();
            case 'groq':
                return new GroqClient();
            case 'ollama':
                return new OllamaClient();
            case 'claude':
                return new ClaudeClient();
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

    public function getFunctions(): array
    {
        return [
            (new SummarizeCollection())->getFunction(),
            (new SearchAndSummarize())->getFunction(),
            (new StandardsChecker())->getFunction(),
        ];
    }

    /**
     * @NOTE
     * Some systems like Ollama might not like all the traffic
     * at once
     */
    public function isAsync(): bool
    {
        return true;
    }
}
