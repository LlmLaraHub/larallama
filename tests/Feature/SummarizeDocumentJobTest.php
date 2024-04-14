<?php

namespace Tests\Feature;

use App\Jobs\SummarizeDocumentJob;
use App\Models\Document;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use Tests\TestCase;

class SummarizeDocumentJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_summary_job(): void
    {

        $data = 'Foo bar';
        $dto = new \LlmLaraHub\LlmDriver\Responses\CompletionResponse($data);

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn($dto);

        $document = Document::factory()->create([
            'summary' => null,
        ]);

        $job = new SummarizeDocumentJob($document);
        $job->handle();

        $this->assertEquals('Foo bar', $document->refresh()->summary);
    }
}
