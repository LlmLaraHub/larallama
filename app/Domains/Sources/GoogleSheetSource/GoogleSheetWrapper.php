<?php

namespace App\Domains\Sources\GoogleSheetSource;

use Illuminate\Support\Facades\Http;

class GoogleSheetWrapper
{
    public function handle(string $sheetId, string $sheetName, string $range = ''): string
    {

        $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/gviz/tq?tqx=out:csv&sheet={$sheetName}";

        if ($range) {
            $url .= "&range={$range}";
        }

        $response = Http::get($url);

        if ($response->successful()) {
            $csvData = $response->body();

            return $csvData;
        }

        return '';
    }
}
