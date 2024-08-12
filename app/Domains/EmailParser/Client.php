<?php

namespace App\Domains\EmailParser;

use App\Jobs\MailBoxParserJob;
use Facades\App\Domains\Sources\EmailSource;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Webklex\IMAP\Facades\Client as ClientFacade;
use Webklex\PHPIMAP\Message;

class Client
{
    protected array $ignore = [
        'Trash',
        'Sent',
        'Spam',
        'Drafts',
    ];

    public function handle(int $limit = 10): void
    {
        $mail = [];

        $client = ClientFacade::account('default');
        $client->connect();

        $folders = $client->getFolders(false);

        foreach ($folders as $folder) {

            $full_name = data_get($folder, 'full_name');
            if (! in_array($full_name, $this->ignore)) {
                $messages = $folder->messages()->all()->limit($limit, 0)->get();

                logger('[LaraChain] - Email Count', [
                    'count' => $messages->count(),
                    'folder' => $full_name,
                ]);

                /** @var Message $message */
                foreach ($messages as $message) {
                    //@NOTE the Seen flag made it too hard to
                    // then have different sources
                    // check the same email box.
                    // the Source will track repeats
                    //$flags = $message->getFlags();

                    $messageDto = $this->getMessageDto($message);

                    \Illuminate\Support\Facades\Log::info('Checking To', [
                        'to' => $message->getTo()->toString(),
                    ]);

                    /**
                     * Just check if it is for this system
                     */
                    $slug = slug_from_email($message->getTo()->toString());

                    if (EmailSource::getSourceFromSlug($slug)) {
                        \Illuminate\Support\Facades\Log::info('Found Source with Slug To', [
                            'to' => $message->getTo()->toString(),
                            'slug' => $slug,
                        ]);
                        $mail[] = new MailBoxParserJob($messageDto);
                        $message->addFlag('Seen');

                    } else {
                        \Illuminate\Support\Facades\Log::info('Did not find Source with Slug To', [
                            'to' => $message->getTo()->toString(),
                            'slug' => $slug,
                        ]);
                    }

                }

            }

            if (! empty($mail)) {
                Bus::batch($mail)
                    ->name('Mail Check '.Str::random(12))
                    ->allowFailures()
                    ->dispatch();
            }
        }
    }

    public function getEmails(string $slug, int $limit = 10): array
    {
        $mail = [];

        $client = ClientFacade::account('default');
        $client->connect();

        $folders = $client->getFolders(false);

        foreach ($folders as $folder) {

            $full_name = data_get($folder, 'full_name');

            if (! in_array($full_name, $this->ignore)) {
                $messages = $folder->messages()->all()->limit($limit, 0)->get();

                logger('[LaraChain] - Email Count', [
                    'count' => $messages->count(),
                    'folder' => $full_name,
                ]);

                /** @var Message $message */
                foreach ($messages as $message) {
                    $messageDto = $this->getMessageDto($message);

                    $incomingSlug = slug_from_email($message->getTo()->toString());

                    if ($incomingSlug === $slug) {
                        \Illuminate\Support\Facades\Log::info('Found Source with Slug To', [
                            'to' => $message->getTo()->toString(),
                            'slug' => $slug,
                        ]);
                        $mail[] = $messageDto;
                        $message->addFlag('Seen');

                    } else {
                        \Illuminate\Support\Facades\Log::info('Did not find Source with Slug To', [
                            'to' => $message->getTo()->toString(),
                            'slug' => $slug,
                        ]);
                    }

                }

            }
        }

        return $mail;
    }

    protected function getMessageDto(Message $message): MailDto
    {
        //@NOTE the Seen flag made it too hard to
        // then have different sources
        // check the same email box.
        // the Source will track repeats
        //$flags = $message->getFlags();

        $messageDto = MailDto::from([
            'to' => $message->getTo()->toString(),
            'from' => $message->getFrom()->toString(),
            'body' => $message->getTextBody(),
            'subject' => $message->getSubject(),
            'date' => $message->getDate()->toString(),
            'header' => $message->getHeader()->raw,
            'email_message' => $message,
        ]);

        return $messageDto;
    }
}
