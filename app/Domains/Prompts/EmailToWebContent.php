<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class EmailToWebContent
{
    public static function prompt(string $context): string
    {

        Log::info('[LaraChain] - EmailToWebContent');

        return <<<'PROMPT'
## TASK ##
The email should have a reference to a web url or many. Return each url and then get the data from each url
making it into a Document in the collection.

## FORMAT ##
JSON ARRAY OF URLs. If no urls return an empty array []


** HERE IS THE EMAIL BODY **
[CONTEXT]
PROMPT;
    }
}
