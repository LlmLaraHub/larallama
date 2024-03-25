<?php

namespace Tests\Feature;

use App\Models\Document;
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
            if (! File::exists($document->mkdirPathToFile())) {
                File::makeDirectory(
                    $document->mkdirPathToFile(),
                    0755,
                    true
                );
            }
            File::copy(
                $from,
                $document->pathToFile()
            );
        }

        $this->document = $document;
    }
}
