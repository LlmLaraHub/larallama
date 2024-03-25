<?php

namespace App\Domains\Documents\Transformers;

use App\Models\Document;
use App\Models\DocumentChunk;
use Smalot\PdfParser\Parser;

class PdfTransformer
{
    protected Document $document;

    public function handle(Document $document): Document
    {
        $this->document = $document;

        $filePath = $this->document->pathToFile();

        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();

        foreach ($pages as $page_number => $page) {
            $page_number = $page_number + 1;
            $pageContent = $page->getText();
            $guid = md5($pageContent);
            DocumentChunk::updateOrCreate(
                [
                    'guid' => $guid,
                    'document_id' => $this->document->id,
                ],
                [
                    'content' => $pageContent,
                    'sort_order' => $page_number,
                ]
            );
        }

        return $this->document;
    }
}
