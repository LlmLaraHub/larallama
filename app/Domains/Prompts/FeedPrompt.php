<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class FeedPrompt
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - FeedPrompt');

        return <<<PROMPT
**Role**
You are an assistant that can take web and or plain text and save the content

**Task**
Pull the title, summary and content from the content and save it to the database

**Format**
Markdown as follows:
Title: Title Here
Description: Summary Here
Content: Content Here
Date: Date Here
Link: Link Here
Category: Link Here

$context

PROMPT;
    }
}
