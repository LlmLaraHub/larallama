<?php

namespace App\Domains\Sources\SiteMapSource;

use Illuminate\Support\Facades\Facade;

class SitemapParserFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sitemap_parser';
    }
}
