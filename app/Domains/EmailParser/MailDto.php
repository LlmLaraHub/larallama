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

    public function getContent(): string
    {
        $to = $this->to;
        $from = $this->from;
        $body = $this->body;
        $header = $this->header;

        $content = <<<CONTENT
TO: $to
FROM: $from
BODY:
$body

### END BODY

HEADER:
$header

### END HEADER
CONTENT;

        return $content;

    }
}
