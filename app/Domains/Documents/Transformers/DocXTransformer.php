<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Helpers\TextChunker;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Element\Image;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\PageBreak;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpWord\IOFactory;

class DocXTransformer
{
    protected Document $document;

    public function handle(Document $document): array
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        $parser = IOFactory::createReader('Word2007');

        if (! File::exists($filePath)) {
            throw new \Exception('Can not fine the document '.$filePath);
        }

        if (! $parser->canRead($filePath)) {
            throw new \Exception('Can not read the document '.$filePath);
        }

        $document = $parser->load($filePath);

        $sections = $document->getSections();

        $content = [];
        $chunks = [];

        foreach ($sections as $section) {
            try {
                $elements = $section->getElements();
                foreach ($elements as $element) {
                    if ($element instanceof Text || $element instanceof TextRun) {
                        $content[] = str($element->getText())->trim()->toString();
                    } elseif ($element instanceof TextBreak) {
                        $content[] = "\n";
                    } elseif ($element instanceof Image) {
                        $content[] = '[Image: '.$element->getSource().']';
                    } elseif ($element instanceof Table) {
                        $content[] = $this->processTable($element);
                    } elseif ($element instanceof ListItem) {
                        $content[] = '[ListItem: '.$element->getText().']';
                    } elseif ($element instanceof PageBreak) {
                        $content[] = "\n";
                    } elseif ($element instanceof Title) {
                        $content[] = $element->getText();
                    } else {
                        Log::info('Unhandled Element', [
                            'class' => get_class($element),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error parsing Docx', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $content_flattened = collect($content)->map(
            function ($item) {
                if ($item instanceof TextRun) {
                    return str($item->getText())->trim()->toString();
                }

                return $item;
            }
        )->filter(
            function ($item) {
                return $item !== '';
            }
        )->implode('');
        $size = config('llmdriver.chunking.default_size');
        $chunked_chunks = TextChunker::handle($content_flattened, $size);
        $page = 1;

        foreach ($chunked_chunks as $chunkSection => $chunkContent) {
            try {
                $DocumentChunk = DocumentChunk::updateOrCreate(
                    [
                        'document_id' => $this->document->id,
                        'sort_order' => $page,
                        'section_number' => $chunkSection,
                    ],
                    [
                        'guid' => md5($chunkContent),
                        'content' => $chunkContent,
                        'meta_data' => [$chunkContent],
                    ]
                );

                $chunks[] = [
                    new VectorlizeDataJob($DocumentChunk),
                ];
            } catch (\Exception $e) {
                Log::error('Error processing Docx', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        notify_collection_ui($this->document->collection,
            CollectionStatusEnum::PROCESSING, 'Processing Document');

        Log::info('DocXTransformer:handle', ['chunks' => count($chunks)]);

        return $chunks;
    }

    private function processTable(Table $table): string
    {
        $tableContent = [];
        $rows = $table->getRows();
        foreach ($rows as $row) {
            $rowData = $this->processRow($row);
            if (! empty($rowData)) {
                $tableContent[] = '[Table Row: '.implode(', ', $rowData).']';
            }
        }

        return implode("\n", $tableContent);
    }

    private function processRow($row): array
    {
        $rowData = [];
        $cells = $row->getCells();
        foreach ($cells as $cell) {
            $cellElements = $cell->getElements();
            foreach ($cellElements as $cellElement) {
                if ($cellElement instanceof Text) {
                    $rowData[] = str($cellElement->getText())->trim()->toString();
                } elseif ($cellElement instanceof TextRun) {
                    $rowData[] = str($cellElement->getText())->trim()->toString();
                } elseif ($cellElement instanceof Table) {
                    $rowData[] = $this->processTable($cellElement);
                }
            }
        }

        return $rowData;
    }
}
