<?php

namespace App\Domains\EmailParser;

use Spatie\LaravelData\Data;

class CredentialsDto extends Data
{
    public function __construct(
        public string $username,
        public string $password,
        public string $host,
        public string $port,
        public string $encryption = 'ssl',
        public string $protocol = 'imap',
        public string $email_box = 'Inbox'
    ) {
    }
}
