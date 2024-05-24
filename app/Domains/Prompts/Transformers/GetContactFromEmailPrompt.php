<?php

namespace App\Domains\Prompts\Transformers;

use Illuminate\Support\Facades\Log;

class GetContactFromEmailPrompt
{
    public static function prompt(string $emailBody, string $who = 'TO'): string
    {
        Log::info('[LaraChain] - GetContactFromEmailPrompt');

        return <<<PROMPT
# Role, Task, Format (R.T.F)
**Role**: Your an agent that is good at finding the TO, FROM, FORWARDED from email copy.
**Task**: Look through this email copy and base on the users WHO find the information, If the message was forwarded the replace the TO with the forwarded address
**Format**: The Format will be json with the following keys

Name: [string]
Email: [string]
Phone: [string]
Socials: [array]

## Examples
{
  Name: "Bob Belcher",
  Email: "bob@bobsburgers.com",
  Phone: +1-111-222-2222
  Socials: [
    "https://facebook.com/bobs_burgers",
    "https://twitter.com/bobs_burgers",
  ]
}

## END EXAMPLE

### WHO (TO, FROM, FORWARDED)
$who

### END WHO

### EMAIL BODY
$emailBody

### END EMAIL BODY

PROMPT;
    }
}
