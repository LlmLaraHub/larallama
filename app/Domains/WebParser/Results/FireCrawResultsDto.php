<?php

namespace App\Domains\WebParser\Results;

use App\Domains\WebParser\WebContentResultsDto;
use Spatie\LaravelData\Attributes\MapInputName;

class FireCrawResultsDto extends WebContentResultsDto
{
    public function __construct(
        #[MapInputName('data.metadata.title')]
        public string $title,
        #[MapInputName('data.markdown')]
        public string $content,
        #[MapInputName('data.content')]
        public string $content_raw,
        #[MapInputName('data.metadata.sourceURL')]
        public string $url,
        #[MapInputName('data.metadata.description')]
        public string $description = '',
    ) {

    }
}
