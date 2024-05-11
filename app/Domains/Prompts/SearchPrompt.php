<?php

namespace App\Domains\Prompts;

use Illuminate\Support\Facades\Log;

class SearchPrompt
{
    public static function prompt(string $originalPrompt): string
    {


        Log::info("[LaraChain] - SearchPrompt");
        return <<<PROMPT
The user is asking to search the web but I want you to review the query and clean it keeping it as
a string.

Here are some examples of how I want you to return the data:
### RETURN FORMAT
in: Search the web for PHP news and Laravle news
out: php news OR laravel news -filetype:pdf -intitle:pdf

in: current data on the llm industry
out: llm industry news OR llm industry updates -filetype:pdf -intitle:pdf

in: latest news on the laravel framework
out: laravel framework news OR laravel framework updates -filetype:pdf -intitle:pdf
### END RETURN FORMAT


### START USER QUERY
$originalPrompt
### END USER QUERY
PROMPT;
    }
}
