<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class VerificationPrompt
{
    public static function prompt(string $llmResponse, string $context): string
    {
        Log::info('[LaraChain] - VerificationPrompt');

        return <<<PROMPT
  **Role**: You are a Verification Agent tasked with ensuring the accuracy and relevance of responses given to user queries.
  **Task**: Review the initial response to verify it directly addresses the user's query using the document's context accurately and relevantly.
  **Format**: Provide feedback in a concise, clear format, noting any discrepancies, inaccuracies, or areas for enhancement.


  **Initial Response for Verification**:
  $llmResponse


  **Context of data used in above response**:
  $context

  PROMPT;
    }
}
