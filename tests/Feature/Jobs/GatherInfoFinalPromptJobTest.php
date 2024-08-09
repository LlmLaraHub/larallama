<?php

namespace Tests\Feature\Jobs;

use App\Jobs\GatherInfoFinalPromptJob;
use App\Models\Message;
use App\Models\Report;
use App\Models\Section;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class GatherInfoFinalPromptJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_final_prompt(): void
    {
        $report = Report::factory()->create(
            [
                'message_id' => Message::factory()->create()->id,
                'user_message_id' => null,
            ]
        );

        LlmDriverFacade::shouldReceive('driver->completion')
            ->twice()
            ->andReturn(
                CompletionResponse::from([
                    'content' => 'Foo bar',
                ])
            );

        Section::factory(3)->create([
            'report_id' => $report->id,
        ]);

        [$job, $batch] = (new GatherInfoFinalPromptJob($report))->withFakeBatch();

        $job->handle();

        $this->assertNotnull($report->user_message_id);

    }
}
