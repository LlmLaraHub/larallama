<?php

namespace App\Domains\WebhookClient;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Client
{

    public  function handle(array $data) : bool|\Exception {
        $token = env('WEBHOOK_TOKEN');
        if (is_null($token)) {
            throw new \Exception('Invalid token');
        }

        $url = env("WEBHOOK_URL");

        if (empty($url)) {
            throw new \Exception('No webhook url');
        }

        $response  = Http::withToken($token)->post($url, $data);

        if ($response->failed()) {
            throw new \Exception($response->getMessage());
        }

        Log::info("Posted to Webhook", $response->json());

        return true;
    }
}
