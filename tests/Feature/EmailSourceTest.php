<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Facades\App\Domains\Sources\EmailSource;
use Tests\TestCase;

class EmailSourceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_slagged_source(): void
    {
        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::EmailSource,
        ]);

        $results = EmailSource::getSourceFromSlug('test');

        $this->assertNotNull($results);

        $results = EmailSource::getSourceFromSlug('foobar');

        $this->assertNull($results);
    }
}
