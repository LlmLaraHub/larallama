<?php 

namespace App\Domains\Sources\WebSearch\Response;

use Spatie\LaravelData\Data;

class WebResponseDto extends Data
{
    public function __construct(
        public string $url,
        public string $title,
        public string|null $age,
        public string|null $description,
        public array $meta_data,
        public string|null $thumbnail,
        public array $profile = []
    ) {}
}