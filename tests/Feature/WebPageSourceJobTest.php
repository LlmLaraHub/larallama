<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Jobs\WebPageSourceJob;
use App\Models\Source;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use Tests\TestCase;

class WebPageSourceJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_makes_documents_triggers_jobs(): void
    {
        Bus::fake();

        LlmDriverFacade::shouldReceive('driver->onQueue')->andReturn('default');

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::WebPageSource,
            'meta_data' => [
                'urls' => 'https://larallama.io/posts/numerous-ui-updates-prompt-template-improvements-and-more
https://docs.larallama.io/developing.html',
            ],
        ]);

        [$job, $batch] = (new WebPageSourceJob($source, 'https://larallama.io/posts/numerous-ui-updates-prompt-template-improvements-and-more'))->withFakeBatch();
        $job->handle();

        Bus::assertBatchCount(1);
    }
}
