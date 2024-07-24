<?php

namespace App\Domains\Sources;

use App\Jobs\WebPageSourceJob;
use App\Models\Source;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class WebPageSource extends BaseSource
{
    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::WebPageSource;

    public static string $description = 'Using a URL it will get the page for you';

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
        $jobs = [];

        $urls = $source->meta_data['urls'];

        $urls = explode("\n", $urls);

        foreach ($urls as $url) {

            $jobs[] = new WebPageSourceJob($source, $url);
        }

        Bus::batch($jobs)
            ->name('Web Pages to Documents - '.$source->subject)
            ->finally(function (Batch $batch) {
                //this is triggered in the PdfTransformer class
            })
            ->allowFailures()
            ->onQueue(LlmDriverFacade::driver($source->getDriver())->onQueue())
            ->dispatch();

    }
}
