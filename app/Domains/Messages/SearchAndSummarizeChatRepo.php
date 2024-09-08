<?php

namespace App\Domains\Messages;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Prompts\SummarizePrompt;
use App\Models\Chat;
use App\Models\DocumentChunk;
use App\Models\Message;
use App\Models\PromptHistory;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use LlmLaraHub\LlmDriver\ToolsHelper;

class SearchAndSummarizeChatRepo
{
    use CreateReferencesTrait;
    use ToolsHelper;

    protected string $response = '';

    public function search(
        Chat $chat,
        Message $message): string
    {

        $input = $message->body;

        Log::info('[LaraChain] Search and Summarize Default Function', [
            'note' => 'Showing input since some system grab the last on the array',
            'input' => $input,
        ]);

        $filter = $message->getFilter();

        $functionDto = FunctionCallDto::from([
            'arguments' => json_encode([
                'prompt' => $input,
            ]),
            'function_name' => 'search_and_summarize',
            'filter' => $filter,
        ]);

        $message = $this->addToolsToMessage($message, $functionDto);

        $originalPrompt = $input;

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
            $message->meta_data
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

        notify_ui($chat, 'Building Summary');

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $chat->chatable->getDriver()
        )->chat([
            MessageInDto::from([
                'content' => $message->getContent().' \n <context> \n'.$contentFlattened,
                'role' => 'user',
            ]),
        ]);

        $this->response = $response->content;

        Log::info('[LaraChain] Summary Results before verification', [
            'response' => $this->response,
        ]);

        if (Feature::active('verification_prompt')) {
            $this->verify($chat, $originalPrompt, $context);
        }

        $assistantMessage = $chat->addInput(
            message: $this->response,
            role: RoleEnum::Assistant,
            meta_data: $message->meta_data);

        PromptHistory::create([
            'prompt' => $contentFlattened,
            'chat_id' => $chat->id,
            'message_id' => $assistantMessage->id,
            /** @phpstan-ignore-next-line */
            'collection_id' => $chat->getChatable()?->id,
        ]);

        $this->saveDocumentReference($assistantMessage, $documentChunkResults);

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
