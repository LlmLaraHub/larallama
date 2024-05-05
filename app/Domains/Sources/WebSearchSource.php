<?php

namespace App\Domains\Sources;

use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use App\Models\Source;
use Illuminate\Support\Facades\Log;

class WebSearchSource extends BaseSource
{
    public function handle(Source $source): void
    {
        Log::info('[LaraChain] - Running WebSearchSource');

        $meta_data = $source->meta_data;
        $search = $source->details;
        $limit = data_get($meta_data, 'limit', 5);
        $driver = data_get($meta_data, 'driver', 'mock');

        /** @var SearchResponseDto $response */
        /** @phpstan-ignore-next-line */
        $response = WebSearchFacade::driver($driver)->search(
            search: $search,
            options: [
                'limit' => $limit,
            ]
        );

        $source->last_run = now();
        $source->save();
    }
}
