<?php

namespace App\Domains\EmailParser;

use Spatie\LaravelData\Data;

class MailDto extends Data
{
    public function __construct(
        public ?string $subject,
        public ?string $from,
        public ?string $to,
        public ?string $body,
        public ?string $header
    ) {
    }
}
