<?php

namespace Tests\Feature;

use App\Jobs\ParsePowerPointJob;
use App\Models\Document;
use Facades\App\Domains\Documents\Transformers\PowerPointTransformer;
use Tests\TestCase;

class ParsePowerPointJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_adds_jobs(): void
    {
        PowerPointTransformer::shouldReceive('handle')
            ->once()
            ->andReturn([1, 2, 3]);

        $document = Document::factory()->pptx()->create();

        [$job, $batch] = (new ParsePowerPointJob($document))->withFakeBatch();

        $job->handle();

    }
}
