<?php

namespace App\Console\Commands;

use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\EmailSource;
use App\Models\Source;
use Illuminate\Console\Command;

class MakeThreadedEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-threaded-emails {source_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make threaded emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emails = get_fixture('emails.json');
        $source = Source::find($this->argument('source_id')); //Assitant Email Source

        foreach ($emails as $email) {
            $dto = MailDto::from($email);

            (new EmailSource())->setMailDto($dto)->handle($source);
        }
    }
}
