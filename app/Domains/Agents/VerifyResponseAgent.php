<?php

namespace App\Domains\Agents;

use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class VerifyResponseAgent extends BaseAgent
{
    public function verify(VerifyPromptInputDto $input): VerifyPromptOutputDto
    {

        Log::info('[LaraChain] VerifyResponseAgent::verify');
        $originalPrompt = $input->originalPrompt;
        $context = $input->context;
        $llmResponse = $input->llmResponse;
        $verifyPrompt = $input->verifyPrompt;

        $prompt = <<<EOT
As a date verification assistant please review the following and return 
a response that cleans up the original "LLM RESPONSE" included below.
What is key for you to do is that this is a RAG systems so if the original "LLM RESPONSE" response does not
line up with the data in the "CONTEXT" then remove any questionable text and 
numbers. See VERIFY PROMPT for any additional information. The output here
will go directly to the user in a chat window so please reply accordingly.
Your Response will not include anything about the verification process you are just a proxy to the origin LLM RESPONSE.
Your Response will be that just cleaned up for chat.
DO NOT include text like "Here is the cleaned-up response" the user should not even know your step happened :) 
Your repsonse will NOT be a list like below but just follow the formatting of the "LLM RESPONSE".

### Included are the following sections
- ORIGINAL PROMPT: The question from the user
- CONTEXT: 
- LLM RESPONSE: The response from the LLM system using the original prompt and context
- VERIFY PROMPT: The prompt added to help clear up the required output.


### START ORIGINAL PROMPT 
{$originalPrompt}
### END ORIGINAL PROMPT

### START CONTEXT
{$context}
### END CONTEXT

### START LLM RESPONSE
{$llmResponse}
### END LLM RESPONSE

### START VERIFY PROMPT
{$verifyPrompt}
### END VERIFY PROMPT

EOT;

        Log::info('[LaraChain] VerifyResponseAgent::verify', [
            'prompt' => $prompt,
        ]);
        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $input->chattable->getDriver()
        )->completion($prompt);

        return VerifyPromptOutputDto::from(
            [
                'chattable' => $input->chattable,
                'originalPrompt' => $input->originalPrompt,
                'context' => $input->context,
                'llmResponse' => $input->llmResponse,
                'verifyPrompt' => $input->verifyPrompt,
                'response' => $response->content,
            ]
        );
    }
}
