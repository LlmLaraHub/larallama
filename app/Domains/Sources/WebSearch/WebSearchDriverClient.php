<?php

namespace App\Domains\Sources\WebSearch;

use App\Domains\Sources\WebSearch\Drivers\BraveSearchClient;
use App\Domains\Sources\WebSearch\Drivers\MockSearchClient;

class WebSearchDriverClient
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
            case 'mock':
                return new MockSearchClient();
            case 'brave':
                return new BraveSearchClient();
            default:
                throw new \InvalidArgumentException("Driver [{$name}] is not supported.");
        }
    }

    public static function getDrivers(): array
    {
        return array_keys(config('llmdriver.sources.search.drivers'));
    }

    protected function getDefaultDriver()
    {
        /**
         * @TODO move this into the collection
         * or override with collection
         */
        return config('llmdriver.sources.search_driver');
    }
}
