<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use Tests\TestCase;

class EnumHelperTraitTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_sources(): void
    {
        $collection = Collection::factory()->create();

        $sources = SourceTypeEnum::getAvailableSources($collection);

        $this->assertNotEmpty($sources);

    }
}
