<?php

namespace Tests\Feature;

use App\Jobs\SummarizeDataJob;
use App\Models\DocumentChunk;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use Tests\TestCase;

class SummarizeDataJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_data(): void
    {

        $data = 'Foo bar';
        $dto = \LlmLaraHub\LlmDriver\Responses\CompletionResponse::from(
            [
                'content' => $data,
                'stop_reason' => 'stop',
            ]
        );

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn($dto);

        $documentChunk = DocumentChunk::factory()->create([
            'summary' => null,
        ]);

        $this->fakeVerify($documentChunk);

        $job = new SummarizeDataJob($documentChunk);
        $job->handle();

        $this->assertEquals('verified yay!', $documentChunk->refresh()->summary);
    }
}
