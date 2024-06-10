<?php

namespace App\Domains\Sources;

use App\Models\Source;
use Illuminate\Support\Facades\Log;
use SimplePie\SimplePie;
use Vedmant\FeedReader\Facades\FeedReader;

class FeedSource extends BaseSource
{
    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::FeedSource;

    public static string $description = 'Get Feeds from Websites and create content from them';

    /**
     * Here you can add content coming in from an API,
     * Email etc to documents. or you can React to the data coming in and for example
     * reply to it from the collection of data in the system eg
     * API hits source with article added to CMS
     * Source triggers Reaction via Output that sends the results of the LLM
     * looking in the collection of data for related content
     *
     * @param Source $source
     * @return void
     */
    public function handle(Source $source): void
    {

        Log::info('[LaraChain] - FeedSource Doing something');


    }

    public function getFeedFromUrl(string $url): array
    {
        /** @var SimplePie $results */
        $results = FeedReader::read($url);

        $items = collect($results->get_items())
            ->transform(
                function ($item) {
                    return [
                        'title' => $item->get_title(),
                        'link' => $item->get_link(),
                        'description' => $item->get_description(),
                        'date' => $item->get_date('Y-m-d H:i:s'),
                        'category' => $item->get_category(),
                        'content' => $item->get_content(),
                    ];
                }
            )->toArray();

        return $items;
    }
}
