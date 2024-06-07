<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class TitlePrompt
{
    public static function prompt(string $documentContent): string
    {
        Log::info('[LaraChain] - Title DocumentPrompt');

        return <<<PROMPT
  **Task**: Read the summary and return a title from it. The title should be in title case.

  **Output**: Just a string in title case that represents a good title for the document no surrounding text.

  Example title output:
  - "How to use the Laravel framework"
  - "The Laravel framework is a powerful PHP web application framework"
  - "Laravel is a PHP web application framework"

  **Content to Summarize**:
$documentContent

PROMPT;
    }
}
