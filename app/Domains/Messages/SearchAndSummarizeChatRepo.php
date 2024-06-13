<?php

namespace App\Domains\Messages;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Prompts\SummarizePrompt;
use App\Models\Chat;
use App\Models\DocumentChunk;
use App\Models\Filter;
use App\Models\PromptHistory;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;

class SearchAndSummarizeChatRepo
{
    use CreateReferencesTrait;

    protected string $response = '';

    public function search(Chat $chat,
        string $input,
        ?Filter $filter = null): string
    {
        Log::info('[LaraChain] Search and Summarize Default Function', [
            'note' => 'Showing input since some system grab the last on the array',
            'input' => $input,
        ]);

        $originalPrompt = $input;

        Log::info('[LaraChain] Embedding the Data', [
            'question' => $input,
        ]);

        /** @var EmbeddingsResponseDto $embedding */
        $embedding = LlmDriverFacade::driver(
            $chat->chatable->getEmbeddingDriver()
        )->embedData($input);

        $embeddingSize = get_embedding_size($chat->chatable->getEmbeddingDriver());

        /** @phpstan-ignore-next-line */
        $documentChunkResults = DistanceQueryFacade::cosineDistance(
            $embeddingSize,
            /** @phpstan-ignore-next-line */
            $chat->getChatable()->id,
            $embedding->embedding,
            $filter
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

        $contentFlattened = SummarizePrompt::prompt(
            originalPrompt: $originalPrompt,
            context: $context
        );

        $chat->addInput(
            message: $contentFlattened,
            role: RoleEnum::Assistant,
            systemPrompt: $chat->chatable->systemPrompt(),
            show_in_thread: false
        );

        /** @TODO coming back to chat shorly just moved to completion to focus on prompt */
        $latestMessagesArray = $chat->getChatResponse();

        Log::info('[LaraChain] Getting the Summary', [
            'input' => $contentFlattened,
            'driver' => $chat->chatable->getDriver(),
            'messages' => count($latestMessagesArray),
        ]);

        notify_ui($chat, 'Building Summary');

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $chat->chatable->getDriver()
        )->chat($latestMessagesArray);

        $this->response = $response->content;

        Log::info('[LaraChain] Summary Results before verification', [
            'response' => $this->response,
        ]);

        if (Feature::active('verification_prompt')) {
            $this->verify($chat, $originalPrompt, $context);
        }

        $message = $chat->addInput($this->response, RoleEnum::Assistant);

        PromptHistory::create([
            'prompt' => $contentFlattened,
            'chat_id' => $chat->id,
            'message_id' => $message->id,
            /** @phpstan-ignore-next-line */
            'collection_id' => $chat->getChatable()?->id,
        ]);

        $this->saveDocumentReference($message, $documentChunkResults);
        notify_ui($chat, 'Complete');

        return $this->response;
    }

    protected function verify(Chat $chat, string $originalPrompt, string $context): void
    {
        $verifyPrompt = <<<'EOD'
        This is the results from a Vector search based on the Users Prompt.
        Then that was passed into the LLM to summarize the results.
        EOD;

        $dto = VerifyPromptInputDto::from(
            [
                'chattable' => $chat,
                'originalPrompt' => $originalPrompt,
                'context' => $context,
                'llmResponse' => $this->response,
                'verifyPrompt' => $verifyPrompt,
            ]
        );

        notify_ui($chat, 'Verifiying Results');

        /** @var VerifyPromptOutputDto $response */
        $response = VerifyResponseAgent::verify($dto);

        $this->response = $response->response;

        Log::info('[LaraChain] Verification', [
            'output' => $this->response,
        ]);
    }
}
