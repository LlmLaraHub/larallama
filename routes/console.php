<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

\Illuminate\Support\Facades\Schedule::command('app:daily-runner')
    ->daily()
    ->at('06:00');

\Illuminate\Support\Facades\Schedule::command('app:hourly-runner')
    ->hourly();

\Illuminate\Support\Facades\Schedule::command('app:half-hourly-runner')
    ->everyThirtyMinutes();

\Illuminate\Support\Facades\Schedule::command('app:check_email')
    ->everyMinute();
