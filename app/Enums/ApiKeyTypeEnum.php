<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum ApiKeyTypeEnum: string
{
    use Values;

    case OPENAI = 'openai';
}
