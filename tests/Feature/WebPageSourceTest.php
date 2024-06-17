<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class WebPageSourceTest extends TestCase
{
    public function test_run()
    {

        Bus::fake();

        $html = get_fixture('test_medium_2.html', false);


        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::WebPageSource,
            'meta_data' => [
                'urls' => 'https://larallama.io/posts/numerous-ui-updates-prompt-template-improvements-and-more
https://docs.larallama.io/developing.html',
            ],
        ]);

        $source->run();

        Bus::assertBatchCount(1);
    }
}
