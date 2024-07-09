<?php

namespace Tests\Feature;

use App\Domains\Documents\TypesEnum;
use App\Jobs\ProcessCSVJob;
use App\Models\DocumentChunk;
use Facades\App\Domains\Documents\Transformers\CSVTransformer;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ProcessCSVJobTest extends TestCase
{
    use SharedSetupForPptFile;

    /**
     * A basic feature test example.
     */
    public function test_batches(): void
    {
        Bus::fake();

        $file = 'strategies.csv';

        $document = $this->setupFile($file);

        $document->update([
            'type' => TypesEnum::CSV,
        ]);

        $file = 'strategies.csv';

        $document2 = $this->setupFile($file);

        CSVTransformer::shouldReceive('handle')
            ->once()->andReturn(
                [
                    $document->id => [
                        DocumentChunk::factory()->create([
                            'document_id' => $document->id,
                        ]),
                        DocumentChunk::factory()->create([
                            'document_id' => $document->id,
                        ]),
                    ],
                    $document2->id => [
                        DocumentChunk::factory()->create([
                            'document_id' => $document->id,
                        ]),
                        DocumentChunk::factory()->create([
                            'document_id' => $document->id,
                        ]),
                    ],
                ]
            );

        [$job, $batch] = (new ProcessCSVJob($document))->withFakeBatch();

        $job->handle();

        Bus::assertBatchCount(2);

    }
}
