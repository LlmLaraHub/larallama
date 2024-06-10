<?php

namespace App\Domains\Documents;

use App\Helpers\EnumHelperTrait;

/**
 * @see settings in config/larachain.php:3
 */
enum TypesEnum: string
{
    use EnumHelperTrait;

    case WebHook = 'web_hook';
    case ScrapeWebPage = 'scrape_web_page';
    case HTML = 'html';
    case PDF = 'pdf';
    case CSV = 'csv';
    case Txt = 'txt';
    case Doc = 'doc';
    case Docx = 'docx';
    case Xls = 'xls';
    case Xlsx = 'xlsx';
    case Ppt = 'ppt';
    case Pptx = 'pptx';

    case Email = 'email';
    case Contact = 'contact';
    case JSON = 'json';
    case Pending = 'pending';

    public static function mimeTypeToType(string $mimeType): TypesEnum
    {

        return match ($mimeType) {
            'application/pdf' => TypesEnum::PDF,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => TypesEnum::Docx,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => TypesEnum::Xlsx,
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => TypesEnum::Pptx,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => TypesEnum::Docx,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => TypesEnum::Xlsx,
            'application/vnd.ms-excel' => TypesEnum::Xls,
            'application/vnd.ms-powerpoint' => TypesEnum::Ppt,
            'text/plain' => TypesEnum::Txt,
            'text/html' => TypesEnum::HTML,
            default => TypesEnum::PDF,
        };

    }
}
