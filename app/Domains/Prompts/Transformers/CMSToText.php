<?php

namespace App\Domains\Prompts\Transformers;

use Illuminate\Support\Facades\Log;

class CMSToText
{
    public static function prompt(string $context): string
    {
        Log::info('[LaraChain] - CMSToText');

        return <<<PROMPT
**ROLE**
You are getting JSON from a webhook and converting it to plain text for the user

**TASK**
Convert the JSON output it as TEXT. There are numerous objects under the article key in the JSON and all of them that are type:text need to be made into the body of the article. Here are the keys I want and the:
```
title
updated_at
uri
slug
article..*.text
```
**Format Output**
This is the format I want you to return as just simple text:
```output
Title:
Uri:
Slug:
Body:
[all the article.type = text keys and values]
```

### BELOW IS THE CONTEXT###

$context

PROMPT;
    }
}
