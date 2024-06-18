<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class EmailReplyPrompt
{
    public static function prompt(string $context, string $emailBeingRepliedTo): string
    {
        Log::info('[LaraChain] - EmailReplyPrompt');

        return <<<PROMPT
**Role**
You are replying to the email that was sent to you.
**Task**
Using the given context and the email that was sent to you, answer the question.
**Format**
Text output that will later be used in an email. Just the body no salutation

**Email being replied to**
$emailBeingRepliedTo

**Context from the system to use as reply context**:
$context

PROMPT;
    }
}
