<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use Tests\TestCase;

class WebSearchSourceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_searches(): void
    {
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

        $websource = new \App\Domains\Sources\WebSearchSource();

        $websource->handle($source);
    }
}
