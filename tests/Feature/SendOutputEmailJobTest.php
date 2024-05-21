<?php

namespace Tests\Feature;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Jobs\SendOutputEmailJob;
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
}
