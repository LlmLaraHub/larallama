<?php

namespace App\Domains\EmailParser;

use App\Jobs\MailBoxParserJob;
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

            /** @var Message $message */
            foreach ($messages as $message) {
                logger('Getting Message');

                $messageDto = MailDto::from([
                    'to' => $message->getTo(),
                    'from' => $message->getFrom(),
                    'body' => $message->getTextBody(),
                    'subject' => $message->getSubject(),
                    'header' => $message->getHeader()->raw,
                ]);

                put_fixture("email_dto.json", $messageDto->toArray());
                //MailBoxParserJob::dispatch($messageDto);
                //$message->delete(expunge: true);
            }
        }
    }
}
