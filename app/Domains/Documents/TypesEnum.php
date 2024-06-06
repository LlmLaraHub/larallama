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

}
