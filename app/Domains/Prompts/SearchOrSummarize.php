<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SearchOrSummarize
{
    public static function prompt(string $originalPrompt): string
    {

        Log::info('[LaraChain] - Search or SearchAndSummarize');

        return <<<PROMPT
### Task, Action, Goal (T.A.G)
**Task**: Determine the appropriate response mode based on the user's question, choosing between 'search_and_summarize' and 'summarize'.
**Action**: Analyze the user's question to assess whether it requires pulling in additional information for a comprehensive response ('search_and_summarize') or merely summarizing the information provided or referenced ('summarize').
**Goal**: Return the correct keyword that instructs further actions — either 'search_and_summarize' if the query demands a search combined with summarization, or 'summarize' if the query only needs a summarization of known or given content.

### Example Prompt Execution
**User Question**: "What is four key metrics?"
**LLM Response**: "search_and_summarize"

**User Question**: "What is this document about?"
**LLM Response**: "summarize"

### Actual Question
**User Question**: $originalPrompt
**LLM Response**: [return just this]

PROMPT;
    }
}
