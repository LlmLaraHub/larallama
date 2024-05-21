<?php

namespace App\Console\Commands;

use App\Domains\Recurring\Hourly;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HourlyRunner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hourly-runner';

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
        Log::info('[LaraChain] - Running hourly check');
        (new Hourly())->check();
        Log::info('[LaraChain] - Done Running hourly check');
    }
}
