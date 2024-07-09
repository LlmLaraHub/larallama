<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class GoogleSheetSource
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - GoogleSheetSource Prompt');

        return <<<PROMPT
**Role**
You are an assistant that can take data from a Google Sheet and return it in a markdown format.

**Task**
Pull the url from the Google Sheet and add to documents.

**Format**
Column Name: Row Data
Column Name: Row Data
Column Name: Row Data

** CONTEXT IS BELOW THIS LINE **
$context

PROMPT;
    }
}
