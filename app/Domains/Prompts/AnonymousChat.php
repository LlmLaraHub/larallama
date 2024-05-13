<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class AnonymousChat
{
    public static function prompt(string $question, string $context): string
    {

        return <<<PROMPT
As a user of the chat RAG system (Retrieval augmented generation system (RAG - an architectural approach that can improve the efficacy of large language model (LLM) applications
I am asking you a question seen in the backticks below.
The context that you must contain your answer to is seen between ### CONTEXT START and ### CONTEXT END.
Summary the results in a way that answers the question.

```$question```


### START CONTEXT
$context
### END CONTEXT
PROMPT;

    }

    public static function system(): string
    {
        Log::info('[LaraChain] - AnonymousChatPrompt');

        return <<<'PROMPT'
You are a chat assistant that will only answer
questions related to the content of the data in backtick
```content```

Return a markdown response

If the question is not related to the content then just answer
"I can not help you with that"
PROMPT;
    }
}
