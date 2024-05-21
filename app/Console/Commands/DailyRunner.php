<?php

namespace App\Console\Commands;

use App\Domains\Recurring\Daily;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyRunner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-runner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run daily sources and outs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('[LaraChain] - Running daily check');
        (new Daily())->check();
        Log::info('[LaraChain] - Done Running daily check');
    }
}
