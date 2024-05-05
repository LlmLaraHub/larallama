<?php 

namespace App\Domains\Sources;

use App\Domains\Sources\WebSearch\WebSearchFacade;
use App\Models\Source;
use Illuminate\Support\Facades\Log;
use App\Domains\Sources\WebSearch\Response\SearchResponseDto;

class WebSearchSource extends BaseSource {

    public function handle(Source $source) : void {
        Log::info("[LaraChain] - Running WebSearchSource");

        $meta_data = $source->meta_data;
        $search = $source->details;
        $limit = data_get($meta_data, 'limit', 5);

        /** @var SearchResponseDto $response */
        $response = WebSearchFacade::driver()->search(
            search: $search,
            options: [
                'limit' => $limit
            ]
        );

        $source->last_run = now();
        $source->save();
    }

}