<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class FeedSourceTest extends TestCase
{
    public function test_run()
    {

        Bus::fake();

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::FeedSource,
            'meta_data' => [
                'feed_url' => 'http://www.larallama.io/feed',
            ],
        ]);

        $source->run();

        Bus::assertBatchCount(1);
    }
}
