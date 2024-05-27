<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class EmailPrompt
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - Default Email Prompt');

        return <<<PROMPT
  **Role**
  You are an email assistant that will summarize emails that came in.
  **Task**
  You will surface any emails to the top that seem urgent or need attention. You will make sure to include the From and summarize each one as concise as possible.
  **Format**
  Deliver the response in a concise, clear Markdown format see the Example Output below.

**Example Output Format***

# Here are the 2 Emails that stand out from the others
## Subject One Here
From: Bob Belcher bob@bobsburgers.com
1-2 line summary here
## Subject Two Here
From: Jimmy Pesto info@pesto.com
1-2 line summary here
# Here are the other emails that came in that seem to need your attention
## Subject Three here
From: Teddy teddy@teddy.com
1 lime summary

**end example output format**


**Context from the database search of emails for used context**:
$context

PROMPT;
    }
}
