<?php

namespace App\Domains\Documents\Transformers;

use Generator;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\Drawing\Gd;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\Table;

class ProcessPpt
{
    public function handle(string $pathToFile): Generator
    {
        $parser = IOFactory::createReader('PowerPoint2007');
        if (! $parser->canRead($pathToFile)) {
            throw new \Exception('Can not read the document '.$pathToFile);
        }

        $phppres = new PhpPresentation();
        $oPHPPresentation = $parser->load($pathToFile);

        foreach ($oPHPPresentation->getAllSlides() as $page_number => $page) {
            try {
                foreach ($page->getShapeCollection() as $shape) {
                    // Check if shape contains text
                    Log::info('Processing PPTX Document', [
                        'page_number' => $page_number,
                        'shape' => class_basename($shape),
                    ]);
                    if ($shape instanceof RichText) {
                        // Get the text from the shapes
                        $page_number = $page_number + 1;
                        $pageContent = $shape->getPlainText();
                        $guid = $pathToFile.'_'.$page_number;

                        yield $pageContent;
                    } elseif ($shape instanceof Table) {
                        $table = $shape->getRows();
                        foreach ($table as $row) {
                            foreach ($row->getCells() as $cell) {
                                $pageContent = $cell->getPlainText();
                                yield $pageContent;
                            }
                        }
                    } elseif ($shape instanceof Gd) {
                        $mimtype = str($shape->getMimeType())->afterLast('/')->toString();
                        $contents = $shape->getContents();
                        $name = Str::random(10);
                        $nameAndType = $name.'.'.$mimtype;
                        $path = storage_path('app/temp/'.$nameAndType);
                        FacadesFile::put($path, $contents);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error processing PPTX Document', [
                    'page_number' => $page_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
