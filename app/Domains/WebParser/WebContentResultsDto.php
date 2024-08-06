<?php

namespace App\Domains\WebParser;

use Spatie\Browsershot\Browsershot;
use Spatie\LaravelData\Data;

class WebContentResultsDto extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public string $url,
        public string $description = '',
        public ?Browsershot $browserShot = null,
    ) {

    }
}
