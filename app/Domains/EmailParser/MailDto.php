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
        public ?string $header,
        public ?string $date
    ) {
    }

    public function getContent(): string
    {
        $to = $this->to;
        $from = $this->from;
        $body = $this->body;
        $subject = $this->subject;
        $date = $this->date;

        $content = <<<CONTENT
TO: $to
FROM: $from
SUBJECT: $subject
DATE: $date
BODY:
$body


CONTENT;

        return $content;

    }
}
