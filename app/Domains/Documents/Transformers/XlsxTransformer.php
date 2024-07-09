<?php

namespace App\Domains\Documents\Transformers;

use App\Domains\Documents\TypesEnum;
use App\Models\Document;

class XlsxTransformer extends CSVTransformer
{
    protected Document $document;

    protected TypesEnum $mimeType = TypesEnum::Xlsx;

    protected string $readerType = \Maatwebsite\Excel\Excel::XLSX;
}
