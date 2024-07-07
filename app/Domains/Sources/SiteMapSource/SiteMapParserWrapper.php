<?php

namespace App\Domains\Sources\SiteMapSource;

use App\Domains\Sources\FeedSource\FeedItemDto;
use Illuminate\Support\Collection;
use vipnytt\SitemapParser;

class SiteMapParserWrapper
{
    public function handle(string $url): Collection
    {
        $parser = new SitemapParser();
        $parser->parse($url);

        $items = collect($parser->getURLs())
            ->transform(
                function ($item) {
                    return FeedItemDto::from(
                        [
                            'title' => data_get($item, 'loc'),
                            'link' => data_get($item, 'loc'),
                            'description' => 'sitemap url '.data_get($item, 'loc'),
                            'date' => data_get($item, 'lastmod'),
                        ]
                    );
                }
            );

        return $items;
    }
}
