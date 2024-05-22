<?php

namespace App\Console\Commands;

use Facades\App\Domains\EmailParser\Client;
use Illuminate\Console\Command;

class CheckEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laralamma:check_email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will check email make sure the llm_assistant.check_email is true';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        logger('Checking Email');
        $this->info('Checking Email');
        Client::handle();
        logger('Done Checking Email');
        $this->info('Done Checking Email');
    }
}
