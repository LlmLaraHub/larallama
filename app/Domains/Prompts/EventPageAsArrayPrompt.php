<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class EventPageAsArrayPrompt
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - EventPageAsArrayPrompt');

        return <<<PROMPT
<ROLE>
You are an AI assistant tasked with extracting event data from website content.

<INSTRUCTIONS>
1. Analyze the provided website HTML content below the <CONTENT> tag.
2. Look for information about events within the content.
3. If no event data is found, respond with a single word: false
4. If event data is found, extract the following information for each event:
   - Event Title
   - Start Date
   - End Date
   - Location
   - Description
   - Any other relevant data
5. Format the extracted data as a JSON array according to the specifications below.

<OUTPUT_FORMAT>
If events are found, return a JSON array with the following structure:

[
  {
    "title": "Event Title",
    "startDate": "Start Date",
    "endDate": "End Date",
    "location": "Location",
    "description": "Description",
    "additionalInfo": "Any other relevant data"
  },
  {
    "title": "Event Title",
    "startDate": "Start Date",
    "endDate": "End Date",
    "location": "Location",
    "description": "Description",
    "additionalInfo": "Any other relevant data"
  }
]

If no events are found, return an empty JSON array: []

<CONTENT>
$context
</CONTENT>

Respond only with the JSON array or 'false' if no events are found. Do not include any explanations or additional text in your response.

PROMPT;
    }
}
