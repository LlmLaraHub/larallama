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

    public function test_non_json()
    {
        Bus::fake();

        $payload = get_fixture('example_github.json');

        LlmDriverFacade::shouldReceive('driver->onQueue')
            ->once()->andReturn('default');

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()->andReturn(
                CompletionResponse::from([
                    'content' => 'Foo Bar',
                ])
            );

        $source = Source::factory()->create();

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_chunks', 1);

        Bus::assertBatchCount(1);

    }

    public function test_prevent_duplicates_github()
    {
        Bus::fake();

        $payload = get_fixture('example_github.json');

        LlmDriverFacade::shouldReceive('driver->onQueue')
            ->times(4)->andReturn('default');

        LlmDriverFacade::shouldReceive('driver->completion')
            ->twice()->andReturn(
                CompletionResponse::from([
                    'content' => get_fixture('github_transformed.json', false),
                ])
            );

        $source = Source::factory()->create();

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        $this->assertDatabaseCount('documents', 2);
        $this->assertDatabaseCount('document_chunks', 2);

        Bus::assertBatchCount(4);

    }

    public function test_prevent_duplicates_statamic()
    {
        Bus::fake();

        $payload = get_fixture('statamic.json');

        $payload['id'] = 'fake_id';
        $payload['content'] = $payload;

        LlmDriverFacade::shouldReceive('driver->onQueue')
            ->times(2)->andReturn('default');

        LlmDriverFacade::shouldReceive('driver->completion')
            ->times(2)->andReturn(
                CompletionResponse::from([
                    'content' => 'Foo Bar',
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
        $this->assertDatabaseCount('document_chunks', 1);

        Bus::assertBatchCount(2);

    }
}
