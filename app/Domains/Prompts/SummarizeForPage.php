<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SummarizeForPage
{
    public static function prompt(string $documentContent): string
    {
        Log::info('[LaraChain] - SummarizeDocumentPrompt');

        return <<<PROMPT
**Role**: You are a summarization agent tasked with creating a concise summary of provided content in markdown format suitable for web display.
**Task**: Analyze and condense the key points of the provided content into a brief, structured markdown summary. The summary should be comprehensive yet succinct, capturing the essence of the content while being mindful of web readability.
**Format**: Produce the summary in markdown format, utilizing appropriate markdown elements like headings, bullet points, and brief paragraphs to ensure the summary is well-organized and easy to read on a web page. Do not wrap the markdown in backticks since I will render it as HTML.

### Content to Summarize:
$documentContent

### Markdown Summary Output:
[Your summarized markdown content here]

PROMPT;
    }
}
