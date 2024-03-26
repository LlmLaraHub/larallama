<?php

namespace Tests\Feature;

use App\Jobs\SummarizeDocumentJob;
use App\LlmDriver\LlmDriverFacade;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SummarizeDocumentJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_summary_job(): void
    {
       
        $data = 'Foo bar';
        $dto = new \App\LlmDriver\Responses\CompletionResponse($data);

        LlmDriverFacade::shouldReceive('completion')
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
