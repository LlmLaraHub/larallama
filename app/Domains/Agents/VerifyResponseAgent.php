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
As a Data Integrity Officer please review the following and return only what remains after you clean it up.
DO NOT include text like "Here is the cleaned-up response" the user should not even know your step happened in the process.
DO NOT get an information outside of this context.
Just return the text as if answering the intial users prompt "ORIGINAL PROMPT"
Using the CONTEXT make sure the LLM RESPONSE is accurent and just clean it up if not.

$verifyPrompt


### START ORIGINAL PROMPT 
$originalPrompt
### END ORIGINAL PROMPT

### START CONTEXT
$context
### END CONTEXT

### START LLM RESPONSE
$llmResponse
### END LLM RESPONSE


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
