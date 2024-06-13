<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Prompts\SummarizeCollectionPrompt;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class SummarizeCollection extends FunctionContract
{
    protected string $name = 'summarize_collection';

    protected string $description = 'NOT FOR SEARCH, This is used when the prompt wants to summarize the entire collection of documents';

    protected string $response = '';

    public function handle(
        array $messageArray,
        HasDrivers $model,
        FunctionCallDto $functionCallDto): FunctionResponse
    {
        Log::info('[LaraChain] SummarizeCollection Function called');

        $usersInput = get_latest_user_content($messageArray);

        $summary = collect([]);

        foreach ($model->getChatable()->documents as $document) {
            $summary->add($document->summary);
        }

        notify_ui($model->getChat(), 'Getting Summary');

        $summary = $summary->implode('\n');

        Log::info('[LaraChain] SummarizeCollection', [
            'token_count_v2' => token_counter_v2($summary),
            'token_count_v1' => token_counter($summary),
        ]);

        $prompt = SummarizeCollectionPrompt::prompt($summary, $usersInput);

        $messagesArray = [];

        $messagesArray[] = MessageInDto::from([
            'content' => $prompt,
            'role' => 'user',
        ]);

        $results = LlmDriverFacade::driver($model->getDriver())->chat($messagesArray);

        $this->response = $results->content;

        notify_ui($model->getChat(), 'Summary complete');

        if (Feature::active('verification_prompt')) {
            Log::info('[LaraChain] Verifying Summary Collection');
            $this->verify($model, 'Can you summarize this collection of data for me.', $summary);
        }

        notify_ui_complete($model->getChat());

        return FunctionResponse::from([
            'content' => $this->response,
            'prompt' => $prompt,
            'requires_followup' => true,
            'documentChunks' => collect([]),
        ]);
    }

    protected function verify(
        HasDrivers $model,
        string $originalPrompt,
        string $context
    ): void {
        /**
         * Lets Verify
         */
        $verifyPrompt = <<<'PROMPT'
This the content from all the documents in this collection.
Then that was passed into the LLM to summarize the results.
PROMPT;

        $dto = VerifyPromptInputDto::from(
            [
                'chattable' => $model->getChat(),
                'originalPrompt' => $originalPrompt,
                'context' => $context,
                'llmResponse' => $this->response,
                'verifyPrompt' => $verifyPrompt,
            ]
        );

        notify_ui($model, 'Verifiying Results');

        /** @var VerifyPromptOutputDto $response */
        $response = VerifyResponseAgent::verify($dto);

        $this->response = $response->response;
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'prompt',
                description: 'The prompt the user is using the search for.',
                type: 'string',
                required: true,
            ),
        ];
    }
}
