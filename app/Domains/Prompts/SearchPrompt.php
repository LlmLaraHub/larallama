<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SearchPrompt
{
    public static function prompt(string $originalPrompt): string
    {

        Log::info('[LaraChain] - SearchPrompt 0.0.1');

        return <<<PROMPT
**Context**: The system processes various web search queries that require specific refinements to ensure that the search results are relevant and focused. These refinements include emphasizing key terms.
**Action**: Modify the original search query by emphasizing keywords relevant to the query's intent applying the format adjustments as required.
**Result**: The refined search queries effectively filter   highlight pertinent information, leading to cleaner and more relevant search results.
**Example**:
- **in**: Search the web for PHP news and Laravel news
  **return**: "php news OR laravel news"
- **in**: current data on the llm industry
  **return**: "llm industry news OR llm industry updates"
- **in**: latest news on the laravel framework
  **return**: "laravel framework news OR laravel framework updates"

### USERS SEARCH QUERY TO TRANSFORM
**in**: $originalPrompt
**return**: [Your transformed search string here on return the string between these brackets]
### END USERS SEARCH QUERY TO TRANSFORM

PROMPT;
    }
}
