<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ClearAllHorizonQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:clear-all-horizon-queues';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear them all!';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queues = config('horizon.defaults');
        foreach ($queues as $keyName => $queue) {
            $names = data_get($queue, 'queue', []);
            foreach ($names as $name) {
                try {
                    $this->info("Clearing queue: $name");
                    Log::info("Clearing queue: $name");
                    Artisan::call('queue:clear  --force --queue '.$name);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                    Log::error($e->getMessage().' '.$name);
                }
            }
        }
    }
}
