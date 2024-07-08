<?php

namespace LlmLaraHub\TagFunction;

class TagPrompt
{
    public static function prompt(string $context): string
    {
        return <<<PROMPT
**ROLE**
You are an assistant to help tag the content

**TASK**
You are going to tag the content, limited to 3 tags

**FORMAT**
Each tag should will be separated by a comma. No other text should be returned.
EXAMPLE FORMAT:
Tag 1, Tag Two Test, Tag Three Test


### START SUMMARY
$context
### END SUMMARY
PROMPT;
    }
}
