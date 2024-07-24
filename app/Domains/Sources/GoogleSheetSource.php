<?php

namespace App\Domains\Sources;

use App\Domains\Documents\TypesEnum;
use App\Jobs\ProcessCSVJob;
use App\Models\Document;
use App\Models\Source;
use Facades\App\Domains\Sources\GoogleSheetSource\GoogleSheetWrapper;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleSheetSource extends BaseSource
{
    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::GoogleSheetSource;

    public static string $description = 'Add an URL that is Public Viewable and the system will keep an eye on it';

    public bool $promptPower = false;

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

        Log::info('[LaraChain] - GoogleSheetSource Doing something');

        $content = GoogleSheetWrapper::handle($source->meta_data['sheet_id'], $source->meta_data['sheet_name']);

        $collection = $source->collection;

        $name = sprintf('%s_%s.csv',
            $source->meta_data['sheet_name'],
            str($source->meta_data['sheet_id'])->limit(15, '')->toString()
        );

        Storage::disk('collections')
            ->put($collection->id.'/'.$name, $content);

        $document = Document::updateOrCreate([
            'collection_id' => $collection->id,
            'file_path' => $name,
            'type' => TypesEnum::CSV,
        ]);

        Bus::batch([
            new ProcessCSVJob($document),
        ])
            ->name('Process Google Sheet CSV Document - '.$document->id)
            ->allowFailures()
            ->dispatch();

        Log::info('[LaraChain] - GoogleSheetSource sent work to ProcessCSVJob');

    }
}
