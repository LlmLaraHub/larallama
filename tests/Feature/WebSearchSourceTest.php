<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class WebSearchSourceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_searches(): void
    {
        Bus::fake();

        WebSearchFacade::shouldReceive('driver->search')
            ->once()
            ->andReturn(
                SearchResponseDto::from([
                    'video' => [],
                    'web' => [],
                ]));

        $source = \App\Models\Source::factory()->create([
            'type' => SourceTypeEnum::WebSearchSource,
        ]);

        $web_source = new \App\Domains\Sources\WebSearchSource();

        $web_source->handle($source);

        Bus::assertBatchCount(1);
    }
}
