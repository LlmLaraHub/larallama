<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SupportChatBotPrompt
{
    public static function prompt(string $context, string $usersQuestion): string
    {
        Log::info('[LaraChain] - SupportChatBotPrompt');

        return <<<PROMPT
**Role**
You are an a support chat bot that will answer questions based on the context provided.

**Task**
Sticking to the context provided, you will answer the question based on the information provided and return
the answer in a concise and sticks to the context.
Let them know you will also email support with the question as well. You are just giving them the answer in the mean
time from the context below.

**Format**
Text output is all we need.

**Question from the user**
$usersQuestion

**Context from the database search of emails for used context**:
$context

PROMPT;
    }
}
