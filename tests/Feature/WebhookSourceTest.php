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

        LlmDriverFacade::shouldReceive('driver->setToolType->chat')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

        $source = Source::factory()->create();

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        $this->assertDatabaseCount('documents', 1);

        Bus::assertBatchCount(1);

    }

    public function test_non_json()
    {
        Bus::fake();

        $payload = get_fixture('example_github.json');

        LlmDriverFacade::shouldReceive('driver->setToolType->chat')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

        $source = Source::factory()->create();

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        $this->assertDatabaseCount('documents', 1);

        Bus::assertBatchCount(1);

    }

    public function test_prevent_duplicates_github()
    {
        Bus::fake();

        $payload = get_fixture('example_github.json');

        LlmDriverFacade::shouldReceive('driver->setToolType->chat')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

        $source = Source::factory()->create();

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        $this->assertDatabaseCount('documents', 1);

        Bus::assertBatchCount(1);

    }

    public function test_prevent_duplicates_statamic()
    {
        Bus::fake();

        $payload = get_fixture('statamic.json');

        $payload['id'] = 'fake_id';
        $payload['content'] = $payload;

        LlmDriverFacade::shouldReceive('driver->setToolType->chat')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

        $source = Source::factory()->create();

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        $this->assertDatabaseCount('documents', 1);

        Bus::assertBatchCount(1);

    }
}
