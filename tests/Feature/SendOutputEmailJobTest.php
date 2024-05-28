<?php

namespace Tests\Feature;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Jobs\SendOutputEmailJob;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Output;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class SendOutputEmailJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_sends(): void
    {
        Mail::fake();
        Event::fake();

        $collection = Collection::factory()->create();

        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'type' => OutputTypeEnum::WebPage,
            'collection_id' => $collection->id,
            'last_run' => null,
        ]);

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        DocumentChunk::factory(5)->create(
            ['document_id' => $document->id]
        );

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()->andReturn(
                CompletionResponse::from([
                    'content' => 'Foo bar',
                ])
            );

        (new SendOutputEmailJob($output))->handle();

        Mail::assertQueuedCount(1);

    }

    public function test_sends_based_on_last_run(): void
    {
        Mail::fake();
        $collection = Collection::factory()->create();

        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'type' => OutputTypeEnum::WebPage,
            'collection_id' => $collection->id,
            'last_run' => now()->subDay(),
        ]);

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        DocumentChunk::factory(5)->create(
            ['document_id' => $document->id]
        );

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()->andReturn(
                CompletionResponse::from([
                    'content' => 'Foo bar',
                ])
            );

        (new SendOutputEmailJob($output))->handle();

        Mail::assertQueuedCount(1);

    }
}
