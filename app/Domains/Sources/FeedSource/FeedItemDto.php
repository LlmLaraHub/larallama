<?php

namespace App\Domains\Sources\FeedSource;

use Spatie\LaravelData\Data;

class FeedItemDto extends Data
{
    public function __construct(
        public string $title,
        public string $link,
        public string $description,
        public string $date,
    ) {

    }
}
