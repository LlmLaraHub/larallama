<?php

namespace App\Console\Commands;

use App\Domains\Recurring\HalfHour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HalfHourRunner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:half-hourly-runner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run hourly sources and outs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('[LaraChain] - Running half hourly check');
        (new HalfHour())->check();
        Log::info('[LaraChain] - Done Running half hourly check');
    }
}
