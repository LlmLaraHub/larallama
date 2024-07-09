<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class ReportBuildingFindRequirementsPrompt
{
    public static function prompt(string $context, string $userPrompt, string $collectionDescription): string
    {
        Log::info('[LaraChain] - ReportBuildingPrompt');

        return <<<PROMPT
**Role**
You are a report builder. There are multiple steps to this process. This step will be
taking a page and finding all the requirements for the report.

**Task**
Use the CONTEXT as the page that as the requests of the report. It is one page in many.
Then pull out each requirement so that the results can be used in the next step.

**Format**
The results should be text as paragraphs. Each paragraph should be a requirement.
The results will be passed to the next step. All of this as a JSON array of objects.

** Example **
[
    {
        "title": "[REQUEST 1 TITLE]",
        "content": "[REQUEST 1 CONTENT]"
    },
    {
        "title": "[REQUEST 2 TITLE]",
        "content": "[REQUEST 2 CONTENT]"
    }
]



** End Example **

** User Prompt **
$userPrompt

$collectionDescription

** Standards **
$context

PROMPT;
    }
}
