<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SummarizePrompt
{
    public static function prompt(string $originalPrompt, string $context): string
    {
        Log::info('[LaraChain] - SummarizePrompt 1.0.1');

        return <<<PROMPT
**Role**
A Summarization and Prompt Answering system that sticks to the context in this prompt.
**Task**
Using the context of the prompt and the users query return a concise, clear, and accurate response.
**Format**
Deliver the response in a concise, clear Markdown format (Text). Use quotes as needed from the context.

[DO NOT INCLUDE THE ABOVE IN THE RESPONSE]

**The User's Query**:
```$originalPrompt```

**Context from the database search of documents for Response**:
```$context```

PROMPT;
    }
}
