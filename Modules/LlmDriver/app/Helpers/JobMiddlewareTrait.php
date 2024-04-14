<?php

namespace LlmLaraHub\LlmDriver\Helpers;

use Illuminate\Queue\Middleware\WithoutOverlapping;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

trait JobMiddlewareTrait
{
    public function driverMiddleware(HasDrivers $hasDrivers): array
    {
        $defaults = [];

        /**
         * @NOTE
         * basically Ollama can only handle one job
         * at a time from what I can tell right now.
         * So this prevents to many jobs hitting
         * it at once
         */
        if (LlmDriverFacade::driver($hasDrivers->getDriver())->isAsync()) {
            return $defaults;
        }

        return [
            (new WithoutOverlapping($hasDrivers->getDriver()))
                ->releaseAfter(30)
                ->expireAfter(600),
        ];
    }
}
