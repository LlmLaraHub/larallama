<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class ReportingSummaryPrompt
{
    public static function prompt(string $solutions): string
    {
        Log::info('[LaraChain] - SummarizeDocumentPrompt');

        return <<<PROMPT
  **Role**
  You are helping to write an solutions list for an RFP. This is the content
  from the system

  **TASK**
  Just summarize the solutions into a bullet list.

  **FORMAT**
  Markdown bullet list

  **Content to Summarize**:
$solutions

PROMPT;
    }
}
