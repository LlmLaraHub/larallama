<?php

namespace Tests\Feature;

use App\Domains\Documents\TypesEnum;
use App\Jobs\ProcessFileJob;
use App\Models\Document;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ProcessFileJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_figures_pptx_type(): void
    {
        Bus::fake();
        $document = Document::factory()->create([
            'type' => TypesEnum::Pptx,
        ]);

        $job = new ProcessFileJob($document);

        $job->handle();

        Bus::assertBatchCount(1);
        Bus::assertBatched(function (PendingBatch $batch) use ($document) {
            return $batch->name === sprintf('Process %s Document - %d', $document->type->value, $document->id);
        });
    }

    public function test_figures_html_type(): void
    {
        Bus::fake();
        $document = Document::factory()->create([
            'type' => TypesEnum::HTML,
        ]);

        $job = new ProcessFileJob($document);

        $job->handle();

        Bus::assertBatchCount(1);
        Bus::assertBatched(function (PendingBatch $batch) use ($document) {
            return $batch->name === sprintf('Process %s Document - %d', $document->type->value, $document->id);
        });
    }

    public function test_figures_txttype(): void
    {
        Bus::fake();
        $document = Document::factory()->create([
            'type' => TypesEnum::Txt,
        ]);

        $job = new ProcessFileJob($document);

        $job->handle();

        Bus::assertBatchCount(1);
        Bus::assertBatched(function (PendingBatch $batch) use ($document) {
            return $batch->name === sprintf('Process %s Document - %d', $document->type->value, $document->id);
        });
    }

    public function test_figures_pdf_type(): void
    {
        Bus::fake();
        $document = Document::factory()->create([
            'type' => TypesEnum::PDF,
        ]);

        $job = new ProcessFileJob($document);

        $job->handle();

        Bus::assertBatched(function (PendingBatch $batch) use ($document) {
            return $batch->name === sprintf('Process %s Document - %d', $document->type->value, $document->id);
        });

    }
}
