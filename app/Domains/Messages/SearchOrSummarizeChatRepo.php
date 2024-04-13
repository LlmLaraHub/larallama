<?php

namespace App\Domains\Messages;

use App\LlmDriver\LlmDriverFacade;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use App\Models\Chat;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;

class SearchOrSummarizeChatRepo
{
    public function search(Chat $chat, string $input): string
    {
        /**
         * @TODO
         * Later using the LLM we will decide if the input is best served
         * by searching the data or a summary of the data.
         * For now we will search.
         */
        Log::info('ChatController:chat getting embedding', ['input' => $input]);

        /** @var EmbeddingsResponseDto $embedding */
        $embedding = LlmDriverFacade::driver(
            $chat->chatable->getEmbeddingDriver()
        )->embedData($input);

        $embeddingSize = get_embedding_size($chat->chatable->getEmbeddingDriver());

        $results = DocumentChunk::query()
            ->join('documents', 'documents.id', '=', 'document_chunks.document_id')
            ->selectRaw(
                "document_chunks.{$embeddingSize} <-> ? as distance, document_chunks.content, document_chunks.{$embeddingSize} as embedding, document_chunks.id as id",
                [$embedding->embedding]
            )
            ->where('documents.collection_id', $chat->chatable->id)
            ->limit(5)
            ->orderByRaw('distance')
            ->get();

        $content = [];

        foreach ($results as $result) {
            $content[] = remove_ascii(reduce_text_size($result->content)); //reduce_text_size seem to mess up Claude?
        }

        $content = implode(' ', $content);

        $content = "This is data from the search results when entering the users prompt which is ### START PROMPT ### {$input} ### END PROMPT ### please use this with the following context and only this and return as markdown so I can render it: ".$content;

        $chat->addInput(
            message: $content,
            role: RoleEnum::User,
            systemPrompt: $chat->chatable->systemPrompt(),
            show_in_thread: false
        );

        $latestMessagesArray = $chat->getChatResponse();

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $chat->chatable->getDriver()
        )->chat($latestMessagesArray);

        $chat->addInput($response->content, RoleEnum::Assistant);

        return $response->content;
    }
}
