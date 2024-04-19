<?php

namespace App\Jobs;

use App\Domains\Documents\TypesEnum;
use App\Models\Document;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use PhpOffice\PhpPresentation\IOFactory;

class ProcessFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $document = $this->document;

        if ($document->type === TypesEnum::Pptx) {
            if (Feature::active('process-ppxt')) {
                /**
                 * Seems to be some work some do not
                 */
                $batch = Bus::batch([
                    new ParsePowerPointJob($this->document),
                ])
                    ->name('Process PPTX Document - '.$document->id)
                    ->finally(function (Batch $batch) use ($document) {
                        SummarizeDocumentJob::dispatch($document);
                    })
                    ->allowFailures()
                    ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                    ->dispatch();
            } else {
                $filePath = $this->document->pathToFile();

                $parser = IOFactory::createReader('PowerPoint2007');
                if (! $parser->canRead($filePath)) {
                    throw new \Exception('Can not read the document '.$filePath);
                }

                /** @phpstan-ignore-next-line */
                $writer = IOFactory::createReader($filePath, 'PDF');

                $filePath = $this->document->pathToFile();

                $filePath = str_replace('.pptx', '.pdf', $filePath);

                /** @phpstan-ignore-next-line */
                $writer->save($filePath);

            }

        } elseif ($document->type === TypesEnum::PDF) {
            $batch = Bus::batch([
                new ParsePdfFileJob($this->document),
            ])
                ->name('Process PDF Document - '.$document->id)
                ->finally(function (Batch $batch) {
                })
                ->allowFailures()
                ->onQueue(LlmDriverFacade::driver($document->getDriver())->onQueue())
                ->dispatch();
        }

    }
}
