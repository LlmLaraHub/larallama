<?php

namespace App\Domains\Chat;

use App\Models\Filter;
use Spatie\LaravelData\Data;

class MetaDataDto extends Data
{
    public function __construct(
        public mixed $persona = '',
        public mixed $filter = null,
        public bool $completion = false,
        public mixed $tool = '',
        public mixed $date_range = '',
        public mixed $input = '',
    ) {

    }

    public function getFilter(): ?Filter
    {
        $filter = data_get($this, 'filter');

        if ($filter) {
            $filter = Filter::findOrFail($filter);
        }

        return $filter;
    }
}
