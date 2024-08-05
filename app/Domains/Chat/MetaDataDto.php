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
        public mixed $tool_id = '',
        public mixed $date_range = '',
        public mixed $input = '',
        public mixed $driver = '',
        public mixed $source = '',
        public mixed $reference_collection_id = '',
        public array $args = []
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
