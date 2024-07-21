<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ChunkDocumentJob;
use App\Models\Document;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ChunkDocumentJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_chunking(): void
    {
        Bus::fake();

        $document = Document::factory()->create();

        $this->assertDatabaseCount('document_chunks', 0);


        [$job, $batch] = (new ChunkDocumentJob($document))->withFakeBatch();


        $job->handle();

        $this->assertDatabaseCount('document_chunks', 1);

        Bus::assertBatchCount(1);
    }
}
