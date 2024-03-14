<?php

namespace App\Domains\Llms;

class LlmWrapper
{
    public static function make(): DriverContract
    {
        $driver = config('template.llm.driver');
        if (! $driver) {
            throw new \Exception('Llm driver not found');
        }

        if (! method_exists((new self), $driver)) {
            throw new \Exception('Llm driver not found');
        }

        return (new self)->$driver();

    }

    protected function openai(): DriverContract
    {
        return OpenAiDriver::make();
    }
}
