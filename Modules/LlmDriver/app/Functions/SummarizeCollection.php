<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class SummarizeCollection extends FunctionContract
{
    protected string $name = 'summarize_collection';

    protected string $description = 'NOT FOR SEARCH, This is used when the prompt wants to summarize the entire collection of documents';

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

        $prompt = <<<PROMPT
Can you summarize all of this content for me from a collection of documents I uploaded what 
follows is the content:

### START ALL SUMMARY DATA
$summary
### END ALL SUMMARY DATA

PROMPT;

        $messagesArray = [];

        $messagesArray[] = MessageInDto::from([
            'content' => $prompt,
            'role' => 'user',
        ]);

        $results = LlmDriverFacade::driver($model->getDriver())->chat($messagesArray);

        notify_ui($model->getChat(), 'Summary complete going to do one verfication check on the summarhy');

        $verifyPrompt = <<<'PROMPT'
        This the content from all the documents in this collection.
        Then that was passed into the LLM to summarize the results.
        PROMPT;

        $dto = VerifyPromptInputDto::from(
            [
                'chattable' => $model->getChat(),
                'originalPrompt' => 'Can you summarize this collection of data for me.',
                'context' => $summary,
                'llmResponse' => $results->content,
                'verifyPrompt' => $verifyPrompt,
            ]
        );

        /** @var VerifyPromptOutputDto $response */
        $response = VerifyResponseAgent::verify($dto);

        return FunctionResponse::from([
            'content' => $response->response,
            'requires_followup' => true,
        ]);
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
