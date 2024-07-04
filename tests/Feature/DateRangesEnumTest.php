<?php

namespace Tests\Feature;

use App\Domains\Chat\DateRangesEnum;
use Tests\TestCase;

class DateRangesEnumTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_dates(): void
    {

        $results = DateRangesEnum::getStartAndEndDates(
            DateRangesEnum::Today->value
        );

        $this->assertEquals(now()->format('Y-m-d'), $results['start']);
        $this->assertEquals(now()->format('Y-m-d'), $results['end']);

        $results = DateRangesEnum::getStartAndEndDates(
            DateRangesEnum::Yesterday->value
        );

        $this->assertEquals(now()->subDay()->format('Y-m-d'), $results['start']);
        $this->assertEquals(now()->subDay()->format('Y-m-d'), $results['end']);

        $results = DateRangesEnum::getStartAndEndDates(
            DateRangesEnum::ThisWeek->value
        );

        $this->assertEquals(now()->subWeek()->format('Y-m-d'), $results['start']);
        $this->assertEquals(now()->format('Y-m-d'), $results['end']);

        $results = DateRangesEnum::getStartAndEndDates(
            DateRangesEnum::LastWeek->value
        );

        $this->assertEquals(now()->subWeeks(2)->format('Y-m-d'), $results['start']);
        $this->assertEquals(now()->subWeek()->format('Y-m-d'), $results['end']);

        $results = DateRangesEnum::getStartAndEndDates(
            DateRangesEnum::ThisMonth->value
        );

        $this->assertEquals(now()->subMonth()->format('Y-m-d'), $results['start']);
        $this->assertEquals(now()->format('Y-m-d'), $results['end']);

        $results = DateRangesEnum::getStartAndEndDates(
            DateRangesEnum::LastMonth->value
        );

        $this->assertEquals(now()->subMonths(2)->format('Y-m-d'), $results['start']);
        $this->assertEquals(now()->subMonth()->format('Y-m-d'), $results['end']);
    }
}
