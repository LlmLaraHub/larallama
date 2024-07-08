<?php

namespace Tests\Feature;

use App\Imports\DocumentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class DocumentsImportTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_imports_csv(): void
    {
        $this->markTestSkipped('@NOTE just not needed but might be later');
        $path = base_path('tests/example-docs/strategies.csv');
        //$results = Excel::import(new DocumentsImport(), $path, null, \Maatwebsite\Excel\Excel::CSV);
        $collection = (new DocumentsImport())->toCollection($path);
    }
}
