<?php

namespace App\Domains\EmailParser;

use Facades\App\Domains\Sources\EmailSource;
use App\Jobs\MailBoxParserJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Webklex\IMAP\Facades\Client as ClientFacade;
use Webklex\PHPIMAP\Message;

class Client
{
    public function handle(): void
    {
        $client = ClientFacade::account('default');
        $client->connect();

        $folders = $client->getFolders(false);

        foreach ($folders as $folder) {
            $full_name = data_get($folder, 'full_name');
            if($full_name === 'INBOX') {
                $messages = $folder->messages()->all()->limit(10, 0)->get();

                logger('[LaraChain] - Email Count', [
                    'count' => $messages->count(),
                    'folder' => $full_name,
                ]);

                $mail = [];
                /** @var Message $message */
                foreach ($messages as $message) {
                    $messageDto = MailDto::from([
                        'to' => $message->getTo(),
                        'from' => $message->getFrom(),
                        'body' => $message->getTextBody(),
                        'subject' => $message->getSubject(),
                        'header' => $message->getHeader()->raw,
                    ]);

                    /**
                     * Just check if it is for this system
                     */
                    if(EmailSource::getSourceFromSlug($messages->to)) {
                        $mail[] = new MailBoxParserJob($messageDto);
                        $message->delete(expunge: true);
                    }

                }

                Bus::batch($mail)
                    ->name('Mail Check '.Str::random(12))
                    ->allowFailures()
                    ->dispatch();
            }
        }
    }
}
