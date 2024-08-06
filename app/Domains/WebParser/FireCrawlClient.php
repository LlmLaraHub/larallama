<?php

namespace App\Domains\WebParser;

use App\Domains\WebParser\Results\FireCrawResultsDto;
use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class FireCrawlClient extends BaseWebParserClient
{
    public function scrape(string $url): WebContentResultsDto
    {
        $results = $this->getClient()->post('/scrape', [
            'url' => $url,
        ]);

        if ($results->failed()) {
            throw new \Exception('FireCrawl API Error '.$results->json());
        }

        $data = $results->json();

        return FireCrawResultsDto::from($data);
    }

    protected function getClient(): PendingRequest
    {
        $url = Setting::getSecret('fire_crawl', 'api_url');
        $token = Setting::getSecret('fire_crawl', 'api_key');

        return Http::baseUrl($url)->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ]);
    }
}
