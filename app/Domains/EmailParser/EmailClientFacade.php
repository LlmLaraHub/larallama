<?php

namespace App\Domains\EmailParser;

use Illuminate\Support\Facades\Facade;

class EmailClientFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'email_client_facade';
    }
}
