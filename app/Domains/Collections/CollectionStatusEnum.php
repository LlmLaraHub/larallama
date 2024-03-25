<?php

namespace App\Domains\Collections;

use App\Helpers\EnumHelperTrait;

enum CollectionStatusEnum: string
{
    use EnumHelperTrait;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PROCESSED = 'processed';
    case FAILED = 'failed';
}
