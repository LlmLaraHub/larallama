<?php

namespace App\Domains\Sources\WebSearch\Drivers;

use App\Domains\Sources\WebSearch\Response\SearchResponseDto;

abstract class BaseSearchClient
{
    public function search(string $search, array $options = []): SearchResponseDto
    {
        /** @phpstan-ignore-next-line */
        return new SearchResponseDto([
            'videos' => [],
            'web' => [],
        ]);
    }
}
