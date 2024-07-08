<?php

namespace Tests\Feature;

use App\Jobs\ProcessCSVJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Facades\App\Domains\Documents\Transformers\CSVTransformer;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ProcessCSVJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_batches(): void
    {
        Bus::fake();

        $document = Document::factory()->create();

        CSVTransformer::shouldReceive('handle')
            ->once()->andReturn(
                [
                    DocumentChunk::factory()->create([
                        'document_id' => $document->id,
                    ]),
                    DocumentChunk::factory()->create([
                        'document_id' => $document->id,
                    ]),
                ]
            );

        [$job, $batch] = (new ProcessCSVJob($document))->withFakeBatch();

        $job->handle();

        Bus::assertBatchCount(2);

    }
}
