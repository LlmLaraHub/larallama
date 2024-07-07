<?php

namespace Tests\Feature;

use App\Domains\Sources\SiteMapSource\SiteMapParserWrapper;
use Tests\TestCase;

class SiteMapParserWrapperTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_sitemap(): void
    {

        $this->markTestSkipped('@TODO Was not abel to mock this');
        $results = (new SiteMapParserWrapper())
            ->handle('https://firehouse.agency/sitemap.xml');
        $this->assertCount(10, $results);

    }
}
