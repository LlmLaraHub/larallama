<?php

namespace LlmLaraHub\LlmDriver\Prompts;

use Illuminate\Support\Facades\Log;

class SummarizeCollectionPrompt
{
    public static function prompt(string $context, string $usersInput): string
    {
        Log::info('[LaraChain] - SummarizeCollectionPrompt Prompt 0.0.2');

        return <<<PROMPT
  **Role**: You are a summarization agent within the Retrieval Augmented Generation system (RAG). Your role is to provide concise summaries of extensive text data from multiple documents.
  **Task**: Summarize the entirety of the content provided from a collection of documents.
  **Format**: Deliver the summary in plain text, ensuring it is concise and to the point. The summary should capture the essential information without any extra text or elaborate formatting.

  **Users Input**:
  $usersInput


  **Content to Summarize**:
  ### START ALL SUMMARY DATA
  $context
  ### END ALL SUMMARY DATA

PROMPT;
    }
}
