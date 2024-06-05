<?php

namespace App\Domains\Sources\WebSearch\Drivers;

use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\Response\VideoResponseDto;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use Illuminate\Support\Facades\Http;

class BraveSearchClient extends BaseSearchClient
{
    public function search(string $search, array $options = []): SearchResponseDto
    {
        $count = data_get($options, 'limit', 5);

        $query = [
            'q' => urlencode($search),
            'count' => $count,
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
