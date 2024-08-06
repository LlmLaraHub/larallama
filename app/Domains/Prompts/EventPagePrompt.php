<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class EventPagePrompt
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - EventPagePrompt');

        return <<<PROMPT
<ROLE>
You are an AI assistant tasked with extracting event data from website content.

<INSTRUCTIONS>
1. Analyze the provided website HTML content below the <CONTENT> tag.
2. Look for information about sporting events within the content.
3. If no event data is found summarize what is on the page
4. If event data is found, extract the following information for each event:
   - Event Title
   - Start Date
   - End Date
   - Location
   - Description
   - Any other relevant data
5. Format the extracted data as a Markdown according to the specifications below.

<OUTPUT_FORMAT>
If events are found, return a Markdown with the following structure for each event on the page:
"title": "Event Title",
"startDate": "Start Date",
"endDate": "End Date",
"location": "Location",
"description": "Description",
"additionalInfo": "Any other relevant data"


If no events are found, return the words "No Content Found" and summarize what was on the page


<CONTENT>
$context
</CONTENT>
PROMPT;
    }
}
