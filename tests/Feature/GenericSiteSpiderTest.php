<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Spiders\GenericSiteSpider;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;
use Tests\TestCase;

class GenericSiteSpiderTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_spider(): void
    {
        $this->markTestSkipped('@NOTE not sure I will keep this');
        $urls = [
            'https://alfrednutile.info/ssh-config',
            'https://alnutile.medium.com/multiple-openai-functions-php-laravel-466cb72eefb8',
        ];

        $collection = Collection::factory()->create();

        $results = Roach::startSpider(
            GenericSiteSpider::class,
            new Overrides(
                startUrls: $urls
            ),
            ['collection' => $collection]
        );

    }
}
