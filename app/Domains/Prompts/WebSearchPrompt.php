<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class WebSearchPrompt
{
    public static function prompt(string $input): string
    {
        Log::info('[LaraChain] - Web Search Prompt');

        return <<<'PROMPT'
GenAI News



PROMPT;
    }
}
