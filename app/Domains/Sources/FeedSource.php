<?php

namespace App\Domains\Sources;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Sources\FeedSource\FeedItemDto;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Jobs\GetWebContentJob;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
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
     */
    public function handle(Source $source): void
    {

        Log::info('[LaraChain] - FeedSource Doing something');

        $feedItems = $this->getFeedFromUrl($source->meta_data['feed_url']);

        $jobs = [];

        foreach ($feedItems as $feedItem) {
            $webResponseDto = WebResponseDto::from([
                'url' => $feedItem['link'],
                'title' => $feedItem['title'],
                'description' => $feedItem['description'],
                'meta_data' => $feedItem,
                'profile' => [],
            ]);
            $jobs[] = new GetWebContentJob($source, $webResponseDto);
        }

        Bus::batch($jobs)
            ->name("Getting Feed Data - {$source->title}")
            ->onQueue(LlmDriverFacade::driver($source->getDriver())->onQueue())
            ->allowFailures()
            ->dispatch();

        $source->last_run = now();
        $source->save();

        notify_collection_ui(
            collection: $source->collection,
            status: CollectionStatusEnum::PENDING,
            message: 'Feed data sent to get all pages and make documents'
        );

    }

    public function getFeedFromUrl(string $url): array
    {
        /** @var SimplePie $results */
        $results = FeedReader::read($url);

        $items = collect($results->get_items())
            ->transform(
                /** @phpstan-ignore-next-line */
                function ($item) {
                    return FeedItemDto::from(
                        [
                            'title' => $item->get_title(),
                            'link' => $item->get_link(),
                            'description' => $item->get_description(),
                            'date' => $item->get_date('Y-m-d H:i:s'),
                        ]
                    );
                }
            )->toArray();

        return $items;
    }
}
