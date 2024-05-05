<?php

namespace App\Domains\Sources;

use App\Helpers\EnumHelperTrait;

enum SourceTypeEnum: string
{
    use EnumHelperTrait;

    case WebSearchSource = 'web_search_source';

}
