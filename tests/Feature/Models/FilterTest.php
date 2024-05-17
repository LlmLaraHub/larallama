<?php

namespace Tests\Feature\Models;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Filter;
use Tests\TestCase;

class FilterTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_filter(): void
    {
        $collection = Collection::factory()->create();
        $filter = Filter::factory()->create(['collection_id' => $collection->id]);
        $documents = Document::factory()->count(5)->create();

        $filter->documents()->attach($documents->pluck('id'));

        $this->assertCount(5, $filter->documents);
        $this->assertEquals($collection->id, $filter->collection->id);

    }
}
