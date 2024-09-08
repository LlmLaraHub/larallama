<?php

namespace App\Domains\Projects\Prompts;

class CampaignPromptTemplate
{

    public static function prompt(): string
    {

        return <<<PROMPT
## Unique Selling Proposition (USP)
[What makes your product/service unique? Why should your target audience choose you over competitors?]

## Key Messages
- [Message 1]
- [Message 2]
- [Message 3]


## Success Metrics
- [Metric 1]: [Target]
- [Metric 2]: [Target]
- [Metric 3]: [Target]

## Social Media

  * Twitter
  * LinkedIn
  * Facebook
  * Medium

## Additional Notes
[Any other important information or considerations for this campaign]

PROMPT;


    }
}
