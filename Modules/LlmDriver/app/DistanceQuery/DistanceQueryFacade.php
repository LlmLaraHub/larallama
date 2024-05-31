<?php

namespace LlmLaraHub\LlmDriver\DistanceQuery;

use Illuminate\Support\Facades\Facade;

class DistanceQueryFacade extends Facade
{
    /**
     * @see DistanceQueryClient
     */
    protected static function getFacadeAccessor(): string
    {
        return 'distance_query_driver';
    }
}
