<?php

namespace LlmLaraHub\LlmDriver\Prompts;

use Illuminate\Support\Facades\Log;

class SummarizeCollectionPrompt
{
    public static function prompt(string $context, string $usersInput): string
    {
        Log::info('[LaraChain] - SummarizeCollectionPrompt Prompt 0.0.2');

        return <<<PROMPT
**Role**
A Summarization and Prompt Answering system that sticks to the context in this prompt.
**Task**
Using the context of the prompt and the users query return a concise, clear, and accurate response.
**Format**
Deliver the response in a concise, clear Markdown format (Text). Use quotes as needed from the context.

  **Users Input**:
  $usersInput


  **Content to Summarize**:
  ### START ALL SUMMARY DATA
  $context
  ### END ALL SUMMARY DATA

PROMPT;
    }
}
