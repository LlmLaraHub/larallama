<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Facades\App\Domains\Sources\SiteMapSource\SiteMapParserWrapper;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SiteMapSourceTest extends TestCase
{
    public function test_run()
    {

        Bus::fake();
        $data = get_fixture('sitemap_parsed_results.json');
        SiteMapParserWrapper::shouldReceive('handle')
            ->once()->andReturn($data);

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::SiteMapSource,
            'meta_data' => [
                'feed_url' => 'https://firehouse.agency/sitemap.xml',
            ],
        ]);

        $source->run();

        Bus::assertBatchCount(1);
    }
}
