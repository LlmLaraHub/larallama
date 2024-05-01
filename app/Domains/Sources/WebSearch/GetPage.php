<?php

namespace App\Domains\Sources\WebSearch;

use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class GetPage
{
    public function handle(string $url): string
    {

        $results = Browsershot::url($url)
            ->dismissDialogs()
            ->fullPage();

        $name = md5($url).'.pdf';
        Storage::disk('collections')->put($name, $results->pdf());

        return $results->bodyHtml();
    }
}
