<?php

namespace App\Domains\Sources;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Jobs\GetWebContentJob;
use App\Models\Source;
use Facades\App\Domains\Sources\SiteMapSource\SiteMapParserWrapper;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class SiteMapSource extends BaseSource
{
    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::SiteMapSource;

    public static string $description = 'Get sites from a sitemap.xml';

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

        Log::info('[LaraChain] - SiteMapSource Doing something');

        /**
         * There is a lot here so we limit them
         */
        $feedItems = SiteMapParserWrapper::handle($source->meta_data['feed_url'])->take(10);

        foreach ($feedItems as $feedItem) {
            $webResponseDto = WebResponseDto::from([
                'url' => $feedItem->link,
                'title' => $feedItem->title,
                'description' => $feedItem->description,
                'meta_data' => $feedItem->toArray(),
                'profile' => [],
            ]);

            Bus::batch([
                new GetWebContentJob($source, $webResponseDto),
            ])
                ->name("Getting Sitemap site for Source - {$webResponseDto->url}")
                ->onQueue(LlmDriverFacade::driver($source->getDriver())->onQueue())
                ->allowFailures()
                ->dispatch();
        }

        $source->last_run = now();
        $source->save();

        notify_collection_ui(
            collection: $source->collection,
            status: CollectionStatusEnum::PENDING,
            message: 'Feed data sent to get all pages and make documents'
        );
    }
}
