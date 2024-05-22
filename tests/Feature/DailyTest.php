<?php

namespace Tests\Feature;

use App\Domains\Recurring\Daily;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Models\Output;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DailyTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_daily(): void
    {
        Bus::fake();

        $source = Source::factory()
            ->create([
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily,
                'last_run' => null,
            ]);

        $output = Output::factory()
            ->create([
                'active' => 1,
                'recurring' => RecurringTypeEnum::Daily,
                'last_run' => null,
            ]);

        (new Daily())->check();

        Bus::assertBatchCount(2);

    }
}
