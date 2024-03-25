<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Source;
use Illuminate\Support\Facades\File;

trait SharedSetupForPdfFile
{
    public Document $document;

    protected function webFileDownloadSetup()
    {
        $document = Document::factory()->create([
            'file_path' => 'example.pdf',
        ]);

        $from = base_path('tests/fixtures/example.pdf');

        if (! File::exists($document->pathToFile())) {
            File::copy(
                $from,
                $document->pathToFile()
            );
        }

        $this->document = $document;
    }
}
