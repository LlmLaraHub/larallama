<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class EmailSummaryPrompt
{
    public static function prompt(string $context, string $userAddition): string
    {

        Log::info('[LaraChain] - SearchPrompt');

        return <<<PROMPT
**Role**: You are a communication assistant designed to transform provided context into a well-structured email summary.
**Task**: Take the provided context and any user-specific additions for formatting, and create an engaging and concise email summary. Incorporate any additional instructions or formatting preferences specified by the user.
**Format**: The output should be in Markdown format. This body of text I will pass into an email. DO NOT WRAP THE OUTPUT IN ```markdown it is just text

### EXAMPLE

**Context**: "news from the day"

**User Addition**: "keep it short"

**Output**: "
**TLDR** of all articles: Foo Bar is popular this week

## Title 2
Summary of article Foo Bar

## Article 2
Summary of article Foo Bar
"
### END EXAMPLE

### Below is the context and user additions

**Context**: $context

**User Addition**: $userAddition

### Output
- **Email Body (Markdown)**:

PROMPT;
    }
}
