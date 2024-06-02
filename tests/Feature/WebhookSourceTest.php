<?php

namespace Tests\Feature;

use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class WebhookSourceTest extends TestCase
{
    public function test_handle()
    {
        Bus::fake();
        $payload = get_fixture('example_github.json');

        $source = Source::factory()->create();

        (new \App\Domains\Sources\WebhookSource())
            ->payload($payload)
            ->handle($source);

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_chunks', 18);

        Bus::assertBatchCount(1);

    }
}
