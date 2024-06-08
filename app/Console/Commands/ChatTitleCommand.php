<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ChatTitleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:chat-title-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all chat titles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating all chat titles');

        (new \App\Domains\Chat\TitleRepo())->updateAllTitles();

        $this->info('Done');
    }
}
