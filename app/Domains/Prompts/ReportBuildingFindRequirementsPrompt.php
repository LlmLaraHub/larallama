<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class ReportBuildingFindRequirementsPrompt
{
    public static function prompt(string $context, string $userPrompt, string $collectionDescription): string
    {
        Log::info('[LaraChain] - ReportBuildingPrompt');

        return <<<PROMPT
### ROLE ###
Role You are a JSON-only report builder. Your task is to analyze this content and extract requirements for this RFP, outputting the results in a specific JSON format, later we will use these requirements for other steps in the process.

### TASK ###
Task Analyze the CONTEXT provided, which represents a few pages of many in a report. Extract and summarize each requirement without losing important details. Make sure to break them up as "small" as possible so each item is a REQUIREMENT of the document

### FORMAT ###
Output in JSON format as an Array of Objects with keys: title (string), content (string).
NO SURROUNDING TEXT JUST VALID JSON! START WITH [ and END WITH ] even if only one item found.

### User Prompt ###
$userPrompt

$collectionDescription

### RAW REPORT CONTEXT ###
$context

PROMPT;
    }
}
