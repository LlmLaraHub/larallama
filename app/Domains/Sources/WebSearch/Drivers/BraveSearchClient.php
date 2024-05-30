<?php

namespace App\Domains\Sources\WebSearch\Drivers;

use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\Response\VideoResponseDto;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use Illuminate\Support\Facades\Http;
use Laravel\Reverb\Loggers\Log;

class BraveSearchClient extends BaseSearchClient
{
    public function search(string $search, array $options = []): SearchResponseDto
    {
        $count = data_get($options, 'limit', 5);

        $last_run = data_get($options, 'last_run', null);

        if($last_run) {
            //last run is carbon object
            //so if it is not null
            //then I want to return
            //YYYY-MM-DDtoYYYY-MM-DD
            //the first date is from the last run
            //the next is the current date
            $last_run = $last_run->subDay()->format('Y-m-d');
            $from = $last_run;
            $to = now()->format('Y-m-d');
        } else {
            //just make from yesterday to today
            $from = now()->subDay()->format('Y-m-d');
            $to = now()->format('Y-m-d');
        }

        $query = [
            'q' => urlencode($search),
            'count' => $count,
            'freshness' => sprintf('%sto%s', $from, $to),
        ];

        \Illuminate\Support\Facades\Log::info('[LaraChain] Brave Search Query', [
            'query' => $query,
        ]);

        $response = $this->getClient()->get('web/search', $query);


        $video_dto = [];
        $web_dto = [];

        $searchResults = $response->json();
        $videos = data_get($searchResults, 'videos.results', []);
        foreach ($videos as $video) {
            $video_dto[] = VideoResponseDto::from([
                'url' => data_get($video, 'url'),
                'title' => data_get($video, 'title'),
                'description' => data_get($video, 'description'),
                'age' => data_get($video, 'age'),
                'thumbnail' => data_get($video, 'thumbnail.src'),
                'meta_data' => data_get($video, 'meta_url'),
            ]);
        }
        $web = data_get($searchResults, 'web.results', []);
        foreach ($web as $page) {
            $web_dto[] = WebResponseDto::from([
                'url' => data_get($page, 'url'),
                'title' => data_get($page, 'title'),
                'description' => data_get($page, 'description'),
                'age' => data_get($page, 'age'),
                'thumbnail' => data_get($page, 'thumbnail.src'),
                'meta_data' => data_get($page, 'profile'),
                'profile' => data_get($page, 'profile'),
            ]);
        }

        return SearchResponseDto::from([
            'videos' => $video_dto,
            'web' => $web_dto,
        ]);
    }

    protected function getClient()
    {
        $api_token = config('llmdriver.sources.config.brave.api_token');

        if (! $api_token) {
            throw new \Exception('Braves token is missing see https://api.search.brave.com/app/documentation/web-search/get-started ');
        }

        return Http::withHeaders([
            'content-type' => 'application/json',
            'accept-encoding' => 'gzip',
            'x-subscription-token' => $api_token,
        ])
            ->timeout(120)
            ->baseUrl('https://api.search.brave.com/res/v1/');
    }
}
