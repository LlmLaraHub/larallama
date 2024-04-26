<?php

namespace App\Domains\Sources\WebSearch;

use Illuminate\Support\Facades\Facade;

class WebSearchFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'web_search_driver';
    }
}
