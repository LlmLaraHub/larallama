<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class StandardsCheckerPrompt
{
    public static function prompt(string $context, string $userPrompt): string
    {
        Log::info('[LaraChain] - StandardsCheckerPrompt');

        return <<<PROMPT
**Role**
You are a standards checker. You will compare the Standards Section with the
information of the User Prompt. The user prompt will include the document to
see if it meets the standards. The prompt might have some other details from the User.

**Task**
Use the Users Prompt section to guide you into any details above and beyond the main goal
of standards checking

**Format**
Return the results as text with places the standards are met and or not.
Quote the Standards you are using for that line item of pass or fail.

** Example **
Your document met 2 of the 3 standards below.
Standard Headline Formatted Title it met.
Standard Not to use Jargon it met.

Standard it did not meet was "No external links"
You can see the standards in the Standards section
and the document you passed in had an extern link to "https://larallama.io"

** End Example **

** User Prompt **
$userPrompt

** Standards **
$context

PROMPT;
    }
}
