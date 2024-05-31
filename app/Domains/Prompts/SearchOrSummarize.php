<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SearchOrSummarize
{
    public static function prompt(string $originalPrompt): string
    {

        Log::info('[LaraChain] - Figure out if SearchOrSummarize Prompt 0.0.2');

        return <<<PROMPT
Determine the appropriate response mode based on the user's question, you are choosing between 'search_and_summarize' and 'summarize'
ONLY RETURN the word search_and_summarize or summarize no other context

### Examples ###
User Question: "What is four key metrics?"
Your Response: "search_and_summarize"
User Question: "What are these documents about?"
LLM Response: "summarize"
### END EXAMPLES ###


### BELOW IS THE ACTUAL QUESTION###
User Question:
$originalPrompt
Your Response:
[search_and_summarize or summarize]

PROMPT;
    }
}
