<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
                $this->info("Clearing queue: $name");
                $this->call('horizon:clear', [$name]);
            }
        }
    }
}
