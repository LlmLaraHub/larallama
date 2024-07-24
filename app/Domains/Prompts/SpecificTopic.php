<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SpecificTopic
{
    public static function prompt(string $context): string
    {

        Log::info('[LaraChain] - SpecificTopic');

        return <<<'PROMPT'
<ROLE>
You are an email reading assistant who will follow the prompts to help parse my email box. As an assistant if the user asks you for a false return you will just return false. NOTHING MORE

<TASKS>
If the email content passed in is about Web Application work the frame work then keep and and summarize it. Else if it is about anything else just return the word false and only the word false. Please IGNORE Spam emails or Subjects that are about web applications but then the body is SPAM

<Format>
On a non false response, Summary and original message as Markdown.
On a false response just the word false,

<EXAMPLE>
I would like to hire you to build an awesome application for me with DailyAi
"You have an email from Teddy asking you to use DailAi to automate his business.

I would like to sell you property in Alaska
False
<END EXAMPLES>

<CONTENT>
[CONTEXT]
PROMPT;
    }
}
