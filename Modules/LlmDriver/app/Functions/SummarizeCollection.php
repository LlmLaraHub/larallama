<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Models\PromptHistory;
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
        Log::info('[LaraChain] SummarizeCollection function called');

        $summary = collect([]);

        /**
         * @TODO
         * Token count???
         */
        foreach ($model->chatable->documents as $document) {
            foreach ($document->document_chunks as $chunk) {
                $summary->add($chunk->summary);
            }
        }

        notify_ui($model->getChat(), 'Getting Summary');

        $summary = $summary->implode('\n');

        $prompt = SummarizeCollectionPrompt::prompt($summary);

        $messagesArray = [];

        $messagesArray[] = MessageInDto::from([
            'content' => $prompt,
            'role' => 'user',
        ]);

        $results = LlmDriverFacade::driver($model->getDriver())->chat($messagesArray);

        $this->response = $results->content;

        notify_ui($model->getChat(), 'Summary complete going to do one verfication check on the summarhy');

        if (Feature::active("verification_prompt")) {
            $this->verify($model, "Can you summarize this collection of data for me.", $summary);
        }

        return FunctionResponse::from([
            'content' => $this->response,
            'prompt' => $prompt,
            'requires_followup' => true,
        ]);
    }


    protected function verify(
        HasDrivers $model,
        string $originalPrompt,
        string $context
        ): void
    {
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
