<?php

namespace App\Console\Commands;

use Facades\App\Domains\EmailParser\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check_email';

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
        if (config('llmlarahub.check_system_email')) {
            Log::info('Checking Email llmlarahub.check_system_email for turning it off');
            Client::handle();
        } else {
            Log::info('Checking email skipped see llmlarahub.check_system_email');
        }
    }
}
