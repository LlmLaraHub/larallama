<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class FindSolutionsPrompt
{
    public static function prompt(string $requirement, string $possibleSolutions, string $collectionDescription): string
    {
        Log::info('[LaraChain] - FindSolutionsPrompt');

        $date = now()->format('Y-m-d');

        return <<<PROMPT
### ROLE ###
Role solutions finder. You will be handed a requirement then the possible strategy or strategies to solve it.

### TASK ###
Using the REQUIREMENT text you will see if the given Strategy is a good fit.
If it is reply with a paragraph or two of text that is a solution to the requirement.
Start each paragraph with a section title.
If the REQUIREMENT is just an address or contact info just note that in your solution
and that is it. If it is contact info do the same.
If it is tasks make a list of those with dates if possible. [Today is $date]

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
