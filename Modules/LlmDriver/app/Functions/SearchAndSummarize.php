<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\SummarizePrompt;
use App\Models\Chat;
use App\Models\PromptHistory;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class SearchAndSummarize extends FunctionContract
{
    use CreateReferencesTrait;

    protected string $name = 'search_and_summarize';

    protected string $description = 'Used to embed users prompt, search database and return summarized results.';

    protected string $response = '';

    /**
     * @param  MessageInDto[]  $messageArray
     */
    public function handle(
        array $messageArray,
        HasDrivers $model,
        FunctionCallDto $functionCallDto
    ): FunctionResponse {
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

        $embedding = LlmDriverFacade::driver(
            $model->getEmbeddingDriver()
        )->embedData($originalPrompt);

        $embeddingSize = get_embedding_size($model->getEmbeddingDriver());

        notify_ui($model, 'Searching documents');

        $documentChunkResults = DistanceQueryFacade::cosineDistance(
            $embeddingSize,
            $model->getChatable()->id,
            $embedding->embedding,
            $functionCallDto->filter,
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

        $contentFlattened = SummarizePrompt::prompt(
            originalPrompt: $originalPrompt,
            context: $context
        );

        $model->getChat()->addInput(
            message: $contentFlattened,
            role: RoleEnum::Assistant,
            systemPrompt: $model->getChat()->getChatable()->systemPrompt(),
            show_in_thread: false
        );

        Log::info('[LaraChain] Getting the Search and Summary results', [
            'input' => $contentFlattened,
            'driver' => $model->getChat()->getChatable()->getDriver(),
        ]);

        $messageArray = MessageInDto::from([
            'content' => $contentFlattened,
            'role' => 'user',
        ]);

        notify_ui($model, 'Building Summary');

        if (! get_class($model) === Chat::class) {
            Log::info('[LaraChain] Using the Simple Completion', [
                'input' => $contentFlattened,
                'driver' => $model->getChatable()->getDriver(),
            ]);
            /** @var CompletionResponse $response */
            $response = LlmDriverFacade::driver(
                $model->getChatable()->getDriver()
            )->completion($contentFlattened);
        } else {
            Log::info('[LaraChain] Using the Chat Completion', [
                'input' => $contentFlattened,
                'driver' => $model->getChatable()->getDriver(),
            ]);
            $messages = $model->getChat()->getChatResponse();

            /** @var CompletionResponse $response */
            $response = LlmDriverFacade::driver(
                $model->getChatable()->getDriver()
            )->chat($messages);
        }

        $this->response = $response->content;

        if (Feature::active('verification_prompt')) {
            $this->verify($model, $originalPrompt, $context);
        }

        $message = $model->getChat()->addInput($this->response, RoleEnum::Assistant);

        PromptHistory::create([
            'prompt' => $contentFlattened,
            'chat_id' => $model->getChat()->id,
            'message_id' => $message?->id,
            'collection_id' => $model->getChat()->getChatable()?->id,
        ]);

        $this->saveDocumentReference($message, $documentChunkResults);

        notify_ui_complete($model->getChat());

        return FunctionResponse::from(
            [
                'content' => $this->response,
                'save_to_message' => false,
                'prompt' => $contentFlattened,
            ]
        );
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
This is the results from a Vector search based on the Users Prompt.
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
                description: 'This is the prompt the user is using to search the database and may or may not assist the results.',
                type: 'string',
                required: false,
            ),
        ];
    }
}
