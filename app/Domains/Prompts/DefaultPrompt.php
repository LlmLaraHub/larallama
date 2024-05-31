<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class DefaultPrompt
{
    public static function prompt(string $originalPrompt, string $context): string
    {
        Log::info('[LaraChain] - SummarizePrompt');

        return <<<PROMPT
  **Role**: As the core Agent of the Retrieval Augmented Generation system (RAG), your primary role is to respond accurately to user queries by interpreting and synthesizing relevant information from provided documents.
  **Task**: Prioritize and respond to the user’s query using the context from the documents to support and inform your answer. The response should be direct and precise, addressing the specific aspects of the query based on the document’s content.
  **Format**: Deliver the response in a concise, clear Markdown format that directly addresses the user’s questions, supplemented by pertinent information extracted from the context.

**The User's Query**:
$originalPrompt

**Context from the database search of documents for Response**:
$context

PROMPT;
    }
}
