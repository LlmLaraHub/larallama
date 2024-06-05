<?php

namespace LlmLaraHub\TagFunction;

class TagPrompt
{
    public static function prompt(string $context): string
    {
        return <<<PROMPT
This is the summary of the document, Can you make some tags I can use. Limit to 1-3 tags.
Please return them as a string of text with each tag separated by a comma for example:
Tag 1, Tag Two Test, Tag Three Test

And nothing else. Here is the summary:
### START SUMMARY
$context
### END SUMMARY
PROMPT;
    }
}
