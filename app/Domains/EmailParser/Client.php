<?php

namespace App\Domains\EmailParser;

use App\Jobs\MailBoxParserJob;
use Facades\App\Domains\Sources\EmailSource;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Laravel\Reverb\Loggers\Log;
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

                \Illuminate\Support\Facades\Log::info("Checking To", [
                    'to' => $message->to
                ]);

                /**
                 * Just check if it is for this system
                 */
                $slug = slug_from_email($message->to);
                if (EmailSource::getSourceFromSlug($slug)) {
                    \Illuminate\Support\Facades\Log::info("Found Source with Slug To", [
                        'to' => $message->to,
                        'slug' => $slug,
                    ]);
                    $mail[] = new MailBoxParserJob($messageDto);
                    $message->delete(expunge: true);
                } else {
                    \Illuminate\Support\Facades\Log::info("Did not find Source with Slug To", [
                        'to' => $message->to,
                        'slug' => $slug,
                    ]);
                }

            }

            Bus::batch($mail)
                ->name('Mail Check '.Str::random(12))
                ->allowFailures()
                ->dispatch();
        }
    }
}
