<?php

namespace App\Domains\Chat;

use App\Helpers\EnumHelperTrait;

enum DateRangesEnum: string
{
    use EnumHelperTrait;

    case Today = 'today';

    case Yesterday = 'yesterday';
    case ThisWeek = 'this_week';
    case LastWeek = 'last_week';
    case ThisMonth = 'this_month';
    case LastMonth = 'last_month';

    public static function getStartAndEndDates(string $dateRange): array|\Exception
    {
        $result = match ($dateRange) {
            self::Today->value => [
                'start' => now(),
                'end' => now(),
            ],
            self::Yesterday->value => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            self::ThisWeek->value => [
                'start' => now()->subWeek()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            self::LastWeek->value => [
                'start' => now()->subWeeks(2)->startOfDay(),
                'end' => now()->subWeek()->endOfDay(),
            ],
            self::ThisMonth->value => [
                'start' => now()->subMonth()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            self::LastMonth->value => [
                'start' => now()->subMonths(2)->startOfDay(),
                'end' => now()->subMonth()->endOfDay(),
            ],
            default => throw new \Exception('Date Range is not supported'),
        };

        return [
            'start' => $result['start']->format('Y-m-d'),
            'end' => $result['end']->format('Y-m-d'),
        ];
    }
}
