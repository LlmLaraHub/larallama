<?php

namespace LlmLaraHub\LlmDriver\DistanceQuery;

use LlmLaraHub\LlmDriver\DistanceQuery\Drivers\Mock;
use LlmLaraHub\LlmDriver\DistanceQuery\Drivers\PostGres;

class DistanceQueryClient
{
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
                return new Mock();
            case 'post_gres':
                return new PostGres();
            default:
                throw new \InvalidArgumentException("Driver [{$name}] is not supported.");
        }
    }

    public function __call($method, $arguments)
    {
        return $this->driver()->$method(...$arguments);
    }

    protected function getDefaultDriver()
    {
        return config('llmdriver.distance_driver');
    }
}
