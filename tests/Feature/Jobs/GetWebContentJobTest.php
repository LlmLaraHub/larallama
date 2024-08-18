<?php

namespace Tests\Feature\Jobs;

use Facades\App\Domains\Sources\CreateDocumentFromSource;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Domains\WebParser\WebContentResultsDto;
use App\Jobs\GetWebContentJob;
use App\Models\Source;
use Facades\App\Domains\Orchestration\OrchestrateVersionTwo;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Bus;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class GetWebContentJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_job_html(): void
    {

        Bus::fake();

        CreateDocumentFromSource::shouldReceive('handle')->once();
        $source = Source::factory()->create();

        $webResponseDto = WebResponseDto::from([
            'url' => 'https://example.com',
            'title' => 'Example',
            'age' => '1 day',
            'description' => 'Example description',
            'meta_data' => ['key' => 'value'],
            'thumbnail' => 'https://example.com/thumbnail.jpg',
            'profile' => ['key' => 'value'],
        ]);

        $html = get_fixture('test_medium_2.html', false);

        GetPage::shouldReceive('make->handle')->once()->andReturn(WebContentResultsDto::from([
            'title' => 'Example',
            'description' => 'Example description',
            'content' => $html,
            'url' => 'https://example.com',
        ]));

        OrchestrateVersionTwo::shouldReceive('sourceOrchestrate')->once();

        LlmDriverFacade::shouldReceive('driver->onQueue')->andReturn('default');

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn(CompletionResponse::from([
                'content' => get_fixture('test_block_of_text.txt', false),
            ]));

        [$job, $batch] = (new GetWebContentJob($source, $webResponseDto))->withFakeBatch();

        $job->handle();

    }
}
