<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Events\CollectionStatusEvent;
use App\Jobs\SummarizeDataJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Shape\RichText;

class PowerPointTransformer
{
    protected Document $document;

    public function handle(Document $document): array
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        $parser = IOFactory::createReader('PowerPoint2007');
        if (! $parser->canRead($filePath)) {
            throw new \Exception('Can not read the document '.$filePath);
        }

        $oPHPPresentation = $parser->load($filePath);

        $chunks = [];
        foreach ($oPHPPresentation->getAllSlides() as $page_number => $page) {
            try {
                foreach ($page->getShapeCollection() as $shape) {
                    // Check if shape contains text
                    if ($shape instanceof RichText) {
                        // Get the text from the shape
                        $page_number = $page_number + 1;
                        $pageContent = $shape->getPlainText();
                        $guid = $filePath.'_'.$page_number;
                        $DocumentChunk = DocumentChunk::updateOrCreate(
                            [
                                'guid' => $guid,
                                'document_id' => $this->document->id,
                            ],
                            [
                                'content' => $pageContent,
                                'sort_order' => $page_number,
                            ]
                        );

                        $chunks[] = [
                            new VectorlizeDataJob($DocumentChunk),
                            new SummarizeDataJob($DocumentChunk),
                            //new TagDataJob($this->document),
                            //then mark it all as done and notify the ui
                        ];
                    }
                }
                CollectionStatusEvent::dispatch($document->collection, CollectionStatusEnum::PROCESSING);
            } catch (\Exception $e) {
                Log::error('Error parsing PDF', ['error' => $e->getMessage()]);
            }
        }

        Log::info('PowerPointTransformer:handle', ['chunks' => count($chunks)]);

        return $chunks;
    }
}
