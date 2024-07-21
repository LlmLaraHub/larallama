<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class EmailToDocumentSummary
{
    public static function prompt(string $context): string
    {

        Log::info('[LaraChain] - EmailToDocumentSummary');

        return <<<'PROMPT'
The following content is from an email. I would like you to summarize it with the following format.

To: **TO HERE**
From: **From Here**
Subject: **Subject Here**
Body:
**Summary Here**


** CONTEXT IS BELOW THIS LINE**
[CONTEXT]
PROMPT;
    }
}
