<?php

namespace App\Domains\Sources;

use App\Domains\Documents\TypesEnum;
use Spatie\LaravelData\Data;

class DocumentDto extends Data
{
    public function __construct(
        public string $link = '',
        public string $title = '',
        public string $subject = '',
        public string $file_path = '',
        public string $document_md5 = '',
        public array $meta_data = [],
        public TypesEnum $type = TypesEnum::Txt,
    ) {

    }
}
