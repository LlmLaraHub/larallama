<?php

namespace App\Domains\Agents;

use App\Domains\Prompts\VerificationPrompt;
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

        $prompt = VerificationPrompt::prompt($llmResponse, $context);

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
