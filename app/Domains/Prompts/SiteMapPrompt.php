<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SiteMapPrompt
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - SiteMapPrompt');

        return <<<PROMPT
**Role**
You are an assistant that can take web and or plain text and save the content

**Task**
Pull the url from the sitemap info and date posted.

**Format**
Markdown as follows:
URL: URL Here
Date: Date Here

** CONTEXT IS BELOW THIS LINE **
$context

PROMPT;
    }
}
