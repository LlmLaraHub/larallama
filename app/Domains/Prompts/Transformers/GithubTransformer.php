<?php

namespace App\Domains\Prompts\Transformers;

use Illuminate\Support\Facades\Log;

class GithubTransformer
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - GithubTransformer');

        return <<<PROMPT
Your Task is to take the fields requested below out of this encoded JSON and return it to the user as the format requested with no surrounding text.

Please take the data from the keys which might be an array and return
as an array of json:

## Keys to use for the Output:
commits.*.id
commits.*.message
commits.*.author.name
commits.*.timestamp
## END KEYS TO USE

## Format You will return the data as will be as follows and ONLY as follows, do not wrap response in ```json ```:
[
  {
    "commit_id": "1f32c2e9e6b8f29206c1b6e72c5f3a85b35c7087",
    "message": "Update README.md by Bob Belcher on 2024-06-01 12:34"
  },
   {
    "commit_id": "1f32c2e9e6b8f29206c1b6e72c5f3a85b35c7084",
    "message": "Update Controller FooBar by Bob Belcher on 2024-06-03 12:34"
  }
]
## END FORMAT

**Context from the database search of emails for used context**:
$context

PROMPT;
    }
}
