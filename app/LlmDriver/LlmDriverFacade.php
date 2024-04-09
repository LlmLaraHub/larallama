<?php

namespace App\LlmDriver;

use Illuminate\Support\Facades\Facade;
use App\LlmDriver\LlmDriverClient;

/**
 * @see LlmDriverClient
 * @package App\LlmDriver
 */
class LlmDriverFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'llm_driver';
    }
}
