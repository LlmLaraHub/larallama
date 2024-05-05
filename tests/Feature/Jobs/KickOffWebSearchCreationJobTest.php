<?php

namespace Tests\Feature\Jobs;

use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use App\Jobs\KickOffWebSearchCreationJob;
use App\Models\Document;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class KickOffWebSearchCreationJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_builds_batch(): void
    {

        $document = Document::factory()->create();

        Bus::fake();

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()->andReturn(CompletionResponse::from([
                'content' => 'updated query',
            ]));

        LlmDriverFacade::shouldReceive('driver->onQueue')
            ->once()->andReturn('ollam');

        WebSearchFacade::shouldReceive('search')->with('updated query', [
            'count' => 5,
        ])->once()
            ->andReturn(
                SearchResponseDto::from([
                    'web' => [
                        WebResponseDto::from([
                            'title' => 'title',
                            'description' => 'description',
                            'url' => 'url',
                            'images' => [],
                            'videos' => [],
                            'meta_data' => [],
                        ]),
                    ],
                    'videos' => [],
                ])
            );

        $job = new KickOffWebSearchCreationJob($document);

        $job->handle();

        Bus::assertBatchCount(1);
    }
}
