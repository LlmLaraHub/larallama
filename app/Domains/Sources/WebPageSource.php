<?php

namespace App\Domains\Sources;

use App\Models\Source;
use Illuminate\Support\Facades\Log;

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

        Log::info('[LaraChain] - WebPageSource Doing something');

    }
}
