<?php

namespace Tests\Feature;

use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class WebhookSourceTest extends TestCase
{
    public function test_handle()
    {
        Bus::fake();

        $payload = get_fixture('example_github.json');

        LlmDriverFacade::shouldReceive('driver->onQueue')
            ->twice()->andReturn('default');

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()->andReturn(
                CompletionResponse::from([
                    'content' => get_fixture('github_transformed.json', false),
                ])
            );

        $source = Source::factory()->create();

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        $this->assertDatabaseCount('documents', 2);
        $this->assertDatabaseCount('document_chunks', 2);

        Bus::assertBatchCount(2);

    }
}
