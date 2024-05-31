<?php

namespace App\Domains\Sources;

use App\Models\Source;
use Illuminate\Support\Facades\Log;

class [RESOURCE_NAME] extends BaseSource
{
    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::[RESOURCE_NAME];

    public static string $description = '[RESOURCE_DESCRIPTION]';

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

        Log::info('[LaraChain] - [RESOURCE_NAME] Doing something');

    }
}
