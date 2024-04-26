<?php

namespace App\Domains\Sources\WebSearch\Response;

use Spatie\LaravelData\Data;

class SearchResponseDto extends Data
{
    /**
     * @param  VideoResponseDto[]  $videos
     * @param  WebResponseDto[]  $web
     * @return void
     */
    public function __construct(
        public array $videos = [],
        public array $web = [],
    ) {
    }

    public function getVideos(): array
    {
        return $this->videos;
    }

    public function getWeb(): array
    {
        return $this->web;
    }
}
