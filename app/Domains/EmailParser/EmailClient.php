<?php

namespace App\Domains\EmailParser;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Message;

class EmailClient
{
    protected array $config = [];

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    protected array $compatible_sources = [
        SourceTypeEnum::EmailSource,
        SourceTypeEnum::EmailBoxSource,
    ];

    /**
     * @return MailDto[]
     *
     * @throws \Webklex\PHPIMAP\Exceptions\ConnectionFailedException
     * @throws \Webklex\PHPIMAP\Exceptions\EventNotFoundException
     * @throws \Webklex\PHPIMAP\Exceptions\FolderFetchingException
     * @throws \Webklex\PHPIMAP\Exceptions\InvalidMessageDateException
     * @throws \Webklex\PHPIMAP\Exceptions\MessageContentFetchingException
     * @throws \Webklex\PHPIMAP\Exceptions\MessageFlagException
     * @throws \Webklex\PHPIMAP\Exceptions\MessageHeaderFetchingException
     * @throws \Webklex\PHPIMAP\Exceptions\MessageNotFoundException
     * @throws \Webklex\PHPIMAP\Exceptions\RuntimeException
     */
    public function handle(Source $source): array
    {
        $mail = [];

        if (! in_array($source->type, $this->compatible_sources)) {
            return $mail;
        }

        $foldersToCheck = explode(',', trim($source->secrets['email_box']));

        $foldersToCheck = collect($foldersToCheck)->map(function ($folder) {
            return str($folder)->lower()->toString();
        })->toArray();

        $secrets = $source->secrets;

        $config = [
            'host' => data_get($secrets, 'host'),
            'port' => data_get($secrets, 'port', 993),
            'protocol' => data_get($secrets, 'protocol', 'imap'),
            'encryption' => data_get($secrets, 'encryption', 'ssl'),
            'username' => data_get($secrets, 'username'),
            'password' => data_get($secrets, 'password'),
        ];

        Log::info('Connecting to email box', [
            'config' => $config,
            'secrets' => $secrets,
        ]);

        $config = [
            'accounts' => [
                'default' => $config,
            ],
        ];

        $client = EmailClientFacade::setConfig($config);

        try {
            $client->connect();

            Log::info('Connected to email box', [
                'host' => data_get($secrets, 'host'),
                'box' => data_get($secrets, 'email_box'),
                'folders_to_check' => $foldersToCheck,
            ]);

            $folders = $client->getFolders(false);

            foreach ($folders as $folder) {
                $full_name = data_get($folder, 'full_name');

                Log::info('Checking folder', [
                    'full_name' => $full_name,
                    'folders_to_check' => $foldersToCheck,
                ]);

                $full_name = str($full_name)->lower()->toString();

                if (in_array($full_name, $foldersToCheck)) {
                    $messages = $folder->messages()->all()->get();
                    logger('[LaraChain] - Email Box Count', [
                        'count' => $messages->count(),
                        'folder' => $full_name,
                    ]);

                    /** @var Message $message */
                    foreach ($messages as $message) {
                        $messageDto = MailDto::from([
                            'to' => $message->getTo()->toString(),
                            'from' => $message->getFrom()->toString(),
                            'body' => $message->getTextBody(),
                            'subject' => $message->getSubject(),
                            'date' => $message->getDate()->toString(),
                            'header' => $message->getHeader()->raw,
                        ]);

                        $mail[] = $messageDto;
                        $message->delete(expunge: true);
                    }

                }
            }

        } catch (\Exception $e) {
            Log::error('Error connecting to email box', [
                'error' => $e->getMessage(),
            ]);

            return $mail;
        }

        return $mail;
    }
}
