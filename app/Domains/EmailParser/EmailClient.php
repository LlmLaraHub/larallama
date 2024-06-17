<?php

namespace App\Domains\EmailParser;

use App\Domains\Sources\SourceTypeEnum;
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
    public function handle(CredentialsDto $credentials, bool $delete = true): array
    {
        $mail = [];

        $foldersToCheck = explode(',', trim($credentials->email_box));

        $foldersToCheck = collect($foldersToCheck)->map(function ($folder) {
            return str($folder)->lower()->toString();
        })->toArray();

        $config = [
            'host' => $credentials->host,
            'port' => $credentials->port,
            'protocol' => $credentials->protocol,
            'encryption' => $credentials->encryption,
            'username' => $credentials->username,
            'password' => $credentials->password,
        ];

        $config = [
            'accounts' => [
                'default' => $config,
            ],
        ];

        $client = EmailClientFacade::setConfig($config);

        try {
            $client->connect();

            Log::info('Connected to email box', [
                'host' => $credentials->host,
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

                        if ($delete) {
                            $message->delete(expunge: true);
                        }
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
