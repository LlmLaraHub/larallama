<?php

namespace App\Helpers;

use SundanceSolutions\LarachainTokenCount\Facades\LarachainTokenCount;

class TokenCountHelper
{
    public static function countTokens(string $content): int
    {
        return LarachainTokenCount::count($content);
    }
}
