<?php

namespace App\Domains\Sources\WebSearch\Response;

use Spatie\LaravelData\Data;

class WebResponseDto extends Data
{
    public function __construct(
        public string $url,
        public string $title,
        public ?string $age,
        public ?string $description,
        public array $meta_data,
        public ?string $thumbnail,
        public array $profile = []
    ) {
    }
}
