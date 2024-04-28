<?php

namespace App\Domains\Messages;

use App\Models\Chat;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\Helpers\DistanceQueryTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;

class SearchAndSummarizeChatRepo
{
    use CreateReferencesTrait, DistanceQueryTrait;

    public function search(Chat $chat, string $input): string
    {
        /**
         * @TODO
         * Later using the LLM we will decide if the input is best served
         * by searching the data or a summary of the data.
         * For now we will search.
         */
        Log::info('[LaraChain] Embedding and Searching');

        /** @var EmbeddingsResponseDto $embedding */
        $embedding = LlmDriverFacade::driver(
            $chat->chatable->getEmbeddingDriver()
        )->embedData($input);

        $embeddingSize = get_embedding_size($chat->chatable->getEmbeddingDriver());

        $documentChunkResults = $this->distance(
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

        $content = implode(' ', $content);

        $content = "This is data from the search results when entering the users prompt which is ### START PROMPT ### {$input} ### END PROMPT ###  please use this with the following context and only this, summarize it for the user and return as markdown so I can render it and strip out and formatting like extra spaces, tabs, periods etc: ".$content;

        $chat->addInput(
            message: $content,
            role: RoleEnum::Assistant,
            systemPrompt: $chat->chatable->systemPrompt(),
            show_in_thread: false
        );

        $latestMessagesArray = $chat->getChatResponse();

        Log::info('[LaraChain] Getting the Summary');

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $chat->chatable->getDriver()
        )->chat($latestMessagesArray);

        $message = $chat->addInput($response->content, RoleEnum::Assistant);

        $this->saveDocumentReference($message, $documentChunkResults);

        return $response->content;
    }
}
