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
You are an assistant helping to get event data from a website.


<TASKS>
If the site has no data about events just return false. Else
return the Event Title, then Start Date, End Date, Location, Description, and any other relevant data.

<FORMAT>
On a non false response you will return the following:
Title: Event Title
Start Date: Start Date
End Date: End Date
Location: Location
Description: Description
<END FORMAT>

** WEBSITE HTML IS BELOW THIS LINE **
<CONTENT>

$context

PROMPT;
    }
}
