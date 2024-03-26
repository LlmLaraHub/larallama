<?php

namespace Tests\Feature;

use App\Jobs\SummarizeDataJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\LlmDriver\LlmDriverFacade;
use App\Models\DocumentChunk;

class SummarizeDataJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_data(): void
    {
        
        $data = "Foo bar";
        $dto = new \App\LlmDriver\Responses\CompletionResponse($data);

        LlmDriverFacade::shouldReceive('completion')
            ->once()
            ->andReturn($dto);

        $documentChunk = DocumentChunk::factory()->create([
            'summary' => null
        ]);

        $job = new SummarizeDataJob($documentChunk);
        $job->handle();

        $this->assertEquals("Foo bar", $documentChunk->refresh()->summary);
    }
}
