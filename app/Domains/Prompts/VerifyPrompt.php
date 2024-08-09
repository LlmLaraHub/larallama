<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class VerifyPrompt
{
    public static function prompt(string $originalResults, string $context): string
    {
        Log::info('[LaraChain] - Verify Prompt');

        return <<<PROMPT
<ROLE>
You are here to verify the results of a previous LLM. Below is the results,
the context for that prompt


<TASK>
Compare the results of the prompt to the data / context it was given and fix any issues.
Return then the fixed results.

<format>
Sae format seen in the original results. Just make sure to not include anything like
"I have checkec the results and they are correct" or anything like that.


<original results>
$originalResults

<context>
$context

PROMPT;
    }
}
