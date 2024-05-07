<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Messages\RoleEnum;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use Facades\LlmLaraHub\LlmDriver\DistanceQuery;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class SearchAndSummarize extends FunctionContract
{
    use CreateReferencesTrait;

    protected string $name = 'search_and_summarize';

    protected string $description = 'Used to embed users prompt, search database and return summarized results.';

    /**
     * @param  MessageInDto[]  $messageArray
     */
    public function handle(
        array $messageArray,
        HasDrivers $model,
        FunctionCallDto $functionCallDto): FunctionResponse
    {
        Log::info('[LaraChain] Using Function: SearchAndSummarize');

        /**
         * @TODO
         *
         * @see https://github.com/orgs/LlmLaraHub/projects/1/views/1?pane=issue&itemId=59671259
         *
         * @TODO
         * Should I break up the string using the LLM to make the search better?
         */
        $input = collect($messageArray)->first(function ($item) {
            return $item->role === 'user';
        });

        $originalPrompt = $input->content;

        /** @var EmbeddingsResponseDto $embedding */
        $embedding = LlmDriverFacade::driver(
            $model->getEmbeddingDriver()
        )->embedData($originalPrompt);

        $embeddingSize = get_embedding_size($model->getEmbeddingDriver());

        notify_ui($model, 'Searching documents');

        $documentChunkResults = DistanceQuery::distance(
            $embeddingSize,
            $model->getChatable()->id,
            $embedding->embedding
        );

        $content = [];

        /**
         * @NOTE
         * Yes this is a lot like the SearchAndSummarizeChatRepo
         * But just getting a sense of things
         */
        foreach ($documentChunkResults as $result) {
            $contentString = remove_ascii($result->content);
            if (Feature::active('reduce_text')) {
                $result = reduce_text_size($contentString);
            }
            $content[] = $contentString; //reduce_text_size seem to mess up Claude?
        }

        $context = implode(' ', $content);

        $contentFlattened = <<<PROMPT
You are a helpful assistant in the RAG system: 
This is data from the search results when entering the users prompt which is 

### START PROMPT 
{$originalPrompt} 
### END PROMPT

Please use this with the following context and only this, summarize it for the user and return as markdown so I can render it and strip out and formatting like extra spaces, tabs, periods etc: 

### START Context
$context
### END Context
PROMPT;

        $model->getChat()->addInput(
            message: $contentFlattened,
            role: RoleEnum::Assistant,
            systemPrompt: $model->getChat()->chatable->systemPrompt(),
            show_in_thread: false
        );

        Log::info('[LaraChain] Getting the Summary from the search results');

        $messageArray = MessageInDto::from([
            'content' => $contentFlattened,
            'role' => 'user',
        ]);

        notify_ui($model, 'Building Summary');

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $model->getChatable()->getDriver()
        )->chat([$messageArray]);

        /**
         * Lets Verify
         */
        $verifyPrompt = <<<'PROMPT'
This is the results from a Vector search based on the Users Prompt.
Then that was passed into the LLM to summarize the results.
PROMPT;

        $dto = VerifyPromptInputDto::from(
            [
                'chattable' => $model->getChat(),
                'originalPrompt' => $originalPrompt,
                'context' => $context,
                'llmResponse' => $response->content,
                'verifyPrompt' => $verifyPrompt,
            ]
        );

        notify_ui($model, 'Verifiying Results');

        /** @var VerifyPromptOutputDto $response */
        $response = VerifyResponseAgent::verify($dto);

        $message = $model->getChat()->addInput($response->response, RoleEnum::Assistant);

        $this->saveDocumentReference($message, $documentChunkResults);

        return FunctionResponse::from(
            [
                'content' => $response->response,
                'save_to_message' => false,
            ]
        );
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'prompt',
                description: 'This is the prompt the user is using to search the database and may or may not assist the results.',
                type: 'string',
                required: false,
            ),
        ];
    }
}
