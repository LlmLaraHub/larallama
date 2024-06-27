<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Support\Facades\File;

trait SharedSetupForPptFile
{
    public Document $document;

    protected function setupFile(string $example_name = 'example.pptx'): Document
    {
        $document = Document::factory()->create([
            'file_path' => $example_name,
        ]);

        $from = base_path('tests/fixtures/sample_data/'.$example_name);

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

        return $document;
    }
}
