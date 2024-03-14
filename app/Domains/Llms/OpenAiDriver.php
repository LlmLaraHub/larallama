<?php

namespace App\Domains\Llms;

use OpenAI;

class OpenAiDriver extends DriverContract
{
    public static function make(): self
    {
        $apiKey = config('openai.api_key');
        $organization = config('openai.organization');
        if (! $apiKey) {
            throw new \Exception('OpenAI API key not found');
        }

        $wrapper = new self;

        $wrapper->client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withOrganization($organization)
            ->withHttpHeader('OpenAI-Beta', 'assistants=v1')
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => config('openai.request_timeout', 30)]))
            ->make();

        return $wrapper;
    }
}
