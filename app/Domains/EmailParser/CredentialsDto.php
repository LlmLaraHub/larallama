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
        public string $protocol,
        public string $encryption,
        public string $email_box = "Inbox"
    ) {
    }

}
