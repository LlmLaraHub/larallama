<?php

namespace Tests\Feature;

use App\Domains\Documents\ChildType;
use App\Domains\Documents\TypesEnum;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Jobs\SendOutputEmailJob;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Output;
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
        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'meta_data' => [
                'to' => 'bob@bobsburgers.com',
            ],
        ]);

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
            'last_run' => now()->subWeek(),
            'collection_id' => $collection->id,
            'meta_data' => [
                'to' => 'bob@bobsburgers.com',
            ],
        ]);

        $parent = Document::factory()->create([
            'type' => TypesEnum::Email,
            'collection_id' => $collection->id,
            'created_at' => now()
        ]);

        $childFrom = Document::factory()->create([
            'parent_id' => $parent->id,
            'collection_id' => $collection->id,
            'type' => TypesEnum::Contact,
            'child_type' => StructuredTypeEnum::EmailTo
        ]);

        $childTo = Document::factory()->create([
            'parent_id' => $parent->id,
            'collection_id' => $collection->id,
            'type' => TypesEnum::Contact,
            'child_type' => StructuredTypeEnum::EmailFrom
        ]);

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
