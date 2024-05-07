<?php

namespace App\Domains\Messages;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Models\Chat;
use App\Models\DocumentChunk;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use Facades\LlmLaraHub\LlmDriver\DistanceQuery;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;

class SearchAndSummarizeChatRepo
{
    use CreateReferencesTrait;

    public function search(Chat $chat, string $input): string
    {
        Log::info('[LaraChain] Search and Summarize Default Function');

        $originalPrompt = $input;

        notify_ui($chat, 'Searching documents');

        Log::info('[LaraChain] Embedding the Data', [
            'question' => $input,
        ]);

        /** @var EmbeddingsResponseDto $embedding */
        $embedding = LlmDriverFacade::driver(
            $chat->chatable->getEmbeddingDriver()
        )->embedData($input);

        $embeddingSize = get_embedding_size($chat->chatable->getEmbeddingDriver());

        $documentChunkResults = DistanceQuery::distance(
            $embeddingSize,
            /** @phpstan-ignore-next-line */
            $chat->getChatable()->id,
            $embedding->embedding
        );

        $content = [];

        /** @var DocumentChunk $result */
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

        $chat->addInput(
            message: $contentFlattened,
            role: RoleEnum::Assistant,
            systemPrompt: $chat->chatable->systemPrompt(),
            show_in_thread: false
        );

        $latestMessagesArray = $chat->getChatResponse();

        Log::info('[LaraChain] Getting the Summary');

        notify_ui($chat, 'Building Summary');

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $chat->chatable->getDriver()
        )->chat($latestMessagesArray);

        /**
         * Lets Verify
         */
        $verifyPrompt = <<<'PROMPT'
This is the results from a Vector search based on the Users Prompt.
Then that was passed into the LLM to summarize the results.
PROMPT;

        $dto = VerifyPromptInputDto::from(
            [
                'chattable' => $chat,
                'originalPrompt' => $originalPrompt,
                'context' => $context,
                'llmResponse' => $response->content,
                'verifyPrompt' => $verifyPrompt,
            ]
        );

        notify_ui($chat, 'Verifiying Results');

        /** @var VerifyPromptOutputDto $response */
        $response = VerifyResponseAgent::verify($dto);

        $message = $chat->addInput($response->response, RoleEnum::Assistant);

        $this->saveDocumentReference($message, $documentChunkResults);

        return $response->response;
    }
}
