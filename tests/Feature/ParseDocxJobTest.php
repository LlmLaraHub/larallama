<?php

namespace Feature;

use App\Jobs\ParseDocxJob;
use App\Models\Document;
use Facades\App\Domains\Documents\Transformers\DocXTransformer;
use Tests\TestCase;

class ParseDocxJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_adds_jobs(): void
    {
        DocXTransformer::shouldReceive('handle')
            ->once()
            ->andReturn([1, 2, 3]);

        $document = Document::factory()->docx()->create();

        [$job, $batch] = (new ParseDocxJob($document))->withFakeBatch();

        $job->handle();

    }
}
