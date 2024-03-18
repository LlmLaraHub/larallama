<?php

namespace App\Domains\Examples;

class ExampleChartData
{
    public static function get()
    {
        return [
            [
                'name' => 'Campaign 1',
                'clicks' => 100,
                'impressions' => 1000,
                'ctr' => 10,
            ],
            [
                'name' => 'Campaign 2',
                'clicks' => 200,
                'impressions' => 1000,
                'ctr' => 20,
            ],
        ];
    }

    public static function asJson(): string
    {
        return json_encode(self::get());
    }
}
