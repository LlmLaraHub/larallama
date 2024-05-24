<?php

namespace App\Domains\Prompts\Transformers;

use Illuminate\Support\Facades\Log;

class GetContactFromEmailPrompt
{
    public static function prompt(string $emailBody, string $emailHeader, string $who = 'TO'): string
    {
        Log::info('[LaraChain] - GetContactFromEmailPrompt');

        return <<<PROMPT
**Role**: Your an agent that is good at finding the TO, FROM, FORWARDED from email copy.
**Task**: Look through this email copy and base on the users WHO find the information, If the message was forwarded the replace the TO with the forwarded address
**Format**: The Format will be json with the following keys

first_name: [string]
last_name: [string] //can be null
email: [string]
phone: [string]
socials: [array]

## EXAMPLE RESPONSE
{
    "first_name": "Bob",
    "last_name": "Belcher",
    "email": "bob@bobsburgers.com",
    "phone": "+1-111-222-2222",
    "socials": [
        "https://facebook.com/bobs_burgers",
        "https://twitter.com/bobs_burgers"
    ]
}

## END EXAMPLE RESPONSE

### START REAL MESSAGE

### FIELD WE ARE LOOKING FOR (TO, FROM, FORWARDED)
$who

### FIELD WE ARE LOOKING FOR

### EMAIL BODY
$emailBody

### END EMAIL BODY

### EMAIL HEADER
$emailHeader

### END EMAIL HEADER

PROMPT;
    }
}
