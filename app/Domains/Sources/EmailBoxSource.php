<?php

namespace App\Domains\Sources;

use App\Domains\EmailParser\CredentialsDto;
use App\Models\Source;
use Facades\App\Domains\EmailParser\EmailClient;
use Facades\App\Domains\Sources\EmailSource as EmailSourceFacade;

class EmailBoxSource extends EmailSource
{
    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::EmailBoxSource;

    public static string $description = 'Email Box Source gets and removes email from an email account of your own.
    Just add the needed connection information and you are good to go.';

    public function handle(Source $source): void
    {
        $credentials = CredentialsDto::from($source->secrets);
        $mails = EmailClient::handle($credentials);
        $this->source = $source;

        foreach ($mails as $mailDto) {
            EmailSourceFacade::setMailDto($mailDto)->handle($source);
        }

    }
}
