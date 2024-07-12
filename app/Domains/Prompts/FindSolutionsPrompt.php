<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class FindSolutionsPrompt
{
    public static function prompt(string $requirement, string $possibleSolutions, string $collectionDescription): string
    {
        Log::info('[LaraChain] - FindSolutionsPrompt');

        return <<<PROMPT
### ROLE ###
Role solutions finder. You will be handed a requirement then the possible strategy or strategies to solve it.

### TASK ###
Using the REQUIREMENT text you will see if the given Strategy is a good fit.
If it is reply with a paragraph or two of text that is a solution to the requirement.
Start each paragraph with a section title.
If it is not just return a blank response. Some requirements are
just contact info and due dates for those just highlight that info.

### FORMAT ###
Output is just Markdown And "" if no solution is found.

### REQUIREMENT ###
$requirement

$collectionDescription

### SOLUTION ###
$possibleSolutions

PROMPT;
    }
}
