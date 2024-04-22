<?php

namespace App\Console\Commands;

use Facades\App\Domains\Documents\Transformers\ProcessPpt;
use Illuminate\Console\Command;

class TestConvertFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-convert-file-command {full_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give the path to the file to see if we can do it or not';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('full_path');
        $results = ProcessPpt::handle($path);

        while ($results->valid()) {
            $this->info($results->current());
            $results->next();
        }
    }
}
