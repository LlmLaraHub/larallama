<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class WebSearchSourceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_searches(): void
    {
        Bus::fake();

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()->andReturn(CompletionResponse::from([
            'content' => 'updated query',
        ]));

        LlmDriverFacade::shouldReceive('driver->onQueue')
            ->once()->andReturn('ollama');

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

        Bus::assertBatchCount(1);
    }
}
