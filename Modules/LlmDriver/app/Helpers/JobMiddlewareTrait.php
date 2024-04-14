<?php

namespace LlmLaraHub\LlmDriver\Helpers;

use LlmLaraHub\LlmDriver\LlmDriverFacade;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use LlmLaraHub\LlmDriver\HasDrivers;

trait JobMiddlewareTrait {

    public function driverMiddleware(HasDrivers $hasDrivers) : array {
        $defaults = [];

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