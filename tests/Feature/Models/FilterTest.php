<?php

namespace Tests\Feature\Models;

use App\Models\Collection;
use App\Models\Filter;
use Tests\TestCase;

class FilterTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_filter(): void
    {
        $collection = Collection::factory()
            ->has(Filter::factory(), 'filters')->create();

        $this->assertNotEmpty($collection->filters);
    }
}
