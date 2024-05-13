<?php

namespace App\Domains\Outputs;

use App\Helpers\EnumHelperTrait;

enum OutputTypeEnum: string
{
    use EnumHelperTrait;

    case WebPage = 'web_page';

}
