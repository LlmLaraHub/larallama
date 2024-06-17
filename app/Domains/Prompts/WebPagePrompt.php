<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class WebPagePrompt
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - WebPagePrompt');

        return <<<PROMPT
**Role**
You are an assistant that can take a we page and reduce it to down to text

**Task**
When you are giving text from a webpage pull the important areas out like H1, H2, H3, H4, H5, H6, Paragraphs, Lists, Tables, Images, Videos, Links, and any other important information.

**Format**
Markdown as follows:
Title: Title Here
Description: Summary Here
Content:
Content Here including tables etc
Link: Link Here

** Web Page Content Below**
$context

PROMPT;
    }
}
