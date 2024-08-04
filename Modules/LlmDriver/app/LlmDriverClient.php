<?php

namespace LlmLaraHub\LlmDriver;

use LlmLaraHub\LlmDriver\Functions\Chat;
use LlmLaraHub\LlmDriver\Functions\CreateDocument;
use LlmLaraHub\LlmDriver\Functions\FunctionContract;
use LlmLaraHub\LlmDriver\Functions\GatherInfoTool;
use LlmLaraHub\LlmDriver\Functions\GetWebSiteFromUrlTool;
use LlmLaraHub\LlmDriver\Functions\ReportingTool;
use LlmLaraHub\LlmDriver\Functions\RetrieveRelated;
use LlmLaraHub\LlmDriver\Functions\SearchTheWeb;
use LlmLaraHub\LlmDriver\Functions\StandardsChecker;
use LlmLaraHub\LlmDriver\Functions\SummarizeCollection;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;

class LlmDriverClient
{
    protected $drivers = [];

    protected ToolTypes $toolType;

    public function setToolType(ToolTypes $toolType): self
    {
        $this->toolType = $toolType;

        return $this;
    }

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
        $functions = collect(
            [
                new SummarizeCollection(),
                new RetrieveRelated(),
                new StandardsChecker(),
                new ReportingTool(),
                new GatherInfoTool(),
                new GetWebSiteFromUrlTool(),
                new SearchTheWeb(),
                new CreateDocument(),
                new Chat(),
            ]
        );

        if (isset($this->toolType)) {
            $functions = $functions->filter(function (FunctionContract $function) {
                return in_array($this->toolType, $function->toolTypes);
            });
        }

        return $functions->toArray();

    }

    public function getFunctionsForUi(): array
    {
        return collect($this->getFunctions())
            ->map(function ($item) {
                $item = $item->toArray();
                $item['name_formatted'] = str($item['name'])->headline()->toString();

                return $item;
            })->toArray();
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
