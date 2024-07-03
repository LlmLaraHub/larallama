<?php

namespace App\Domains\Chat;

use Spatie\LaravelData\Data;

class MetaDataDto extends Data
{

    public function __construct(
        public mixed $persona = '',
        public mixed $filter = '',
        public bool $completion = false,
        public string $tool = '',
        public string $date_range = '',
        public string $input = '',
    ) {

    }
}
