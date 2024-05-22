<?php

namespace App\Domains\EmailParser;

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
            $messages = $folder->messages()->all()->limit(3, 0)->get();

            logger('Emails', [
                $messages->count(),
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

                $mail[] = new MailBoxParserJob($messageDto);

                //$message->delete(expunge: true);
            }

            Bus::batch($mail)
                ->name('Mail Check '.Str::random(12))
                ->allowFailures()
                ->dispatch();

        }
    }
}
