<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\UnStructured\StructuredTypeEnum;
use Generator;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\Drawing\Gd;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\Table;

class ProcessPpt extends BaseTransformer
{
    public function handle(string $pathToFile): Generator
    {
        $parser = IOFactory::createReader('PowerPoint2007');
        if (! $parser->canRead($pathToFile)) {
            throw new \Exception('Can not read the document '.$pathToFile);
        }

        $phppres = new PhpPresentation();
        $oPHPPresentation = $parser->load($pathToFile);

        $documentProperties = $oPHPPresentation->getDocumentProperties();
        $this->creator = $documentProperties->getCreator();
        $this->title = $documentProperties->getTitle();
        $this->last_updated_by = $documentProperties->getLastModifiedBy();
        $this->subject = $documentProperties->getSubject();
        $this->keywords = $documentProperties->getKeywords();
        $this->category = $documentProperties->getCategory();
        $this->description = $documentProperties->getDescription();
        $this->updated_at = $documentProperties->getModified();

        foreach ($oPHPPresentation->getAllSlides() as $page_number => $page) {

            $page_number = $page_number + 1;

            try {
                foreach ($page->getShapeCollection() as $shapeCount => $shape) {
                    /**
                     * @TODO
                     * Chart
                     * List
                     */
                    if ($shape instanceof RichText) {
                        $pageContent = $shape->getPlainText();
                        $guid = $shape->getHashCode();

                        $content = $this->output(
                            type: StructuredTypeEnum::Narrative,
                            content: $pageContent,
                            page_number: $page_number,
                            guid: $guid,
                            element_depth: $shapeCount,
                            is_continuation: $shapeCount > 0,
                        );

                        yield $content;
                    } elseif ($shape instanceof Table) {
                        $table = $shape->getRows();
                        $this->title = 'Table';
                        $this->subject = 'Table';

                        $content = $this->output(
                            type: StructuredTypeEnum::Table,
                            content: 'table data',
                            page_number: $page_number,
                            guid: $shape->getHashCode(),
                            element_depth: 0,
                            is_continuation: false,
                        );
                        yield $content;

                        foreach ($table as $rowNumber => $row) {
                            foreach ($row->getCells() as $cellNumber => $cell) {
                                $pageContent = $cell->getPlainText();

                                $content = $this->output(
                                    type: StructuredTypeEnum::TableRow,
                                    content: $pageContent,
                                    page_number: $page_number,
                                    guid: $row->getHashCode(),
                                    element_depth: $rowNumber.$cellNumber,
                                    is_continuation: true,
                                );
                                yield $content;
                            }
                        }
                    } elseif ($shape instanceof Gd) {
                        $mimtype = str($shape->getMimeType())->afterLast('/')->toString();
                        $contents = $shape->getContents();
                        $this->title = Str::random(10);
                        $nameAndType = $this->title.'.'.$mimtype;
                        $this->path_to_file = storage_path('app/temp/'.$nameAndType);
                        FacadesFile::put($this->path_to_file, $contents);
                        $this->subject = 'image';
                        $this->description = 'Image of type '.$mimtype;

                        $content = $this->output(
                            type: StructuredTypeEnum::Image,
                            content: 'see image', //ocr at this point or after
                            page_number: $page_number,
                            guid: $shape->getHashCode(),
                            element_depth: $shapeCount,
                            is_continuation: false,
                        );
                        yield $content;
                    } else {
                        Log::info('Missing PPTX Content types', [
                            'page_number' => $page_number,
                            'shape' => class_basename($shape),
                        ]);
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
