<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\SummarizePrompt;
use App\Models\Chat;
use App\Models\Message;
use App\Models\PromptHistory;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\Helpers\CreateReferencesTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class SearchAndSummarize extends FunctionContract
{
    use CreateReferencesTrait;

    protected string $name = 'search_and_summarize';

    public bool $showInUi = false;

    public array $toolTypes = [
        ToolTypes::ChatCompletion,
    ];

    protected string $description = 'Used to embed users prompt, search database and return summarized results.';

    protected string $response = '';

    public function handle(
        Message $message): FunctionResponse
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
        $input = $message->getContent();

        $originalPrompt = $input;

        $embedding = LlmDriverFacade::driver(
            $message->getEmbeddingDriver()
        )->embedData($originalPrompt);

        $embeddingSize = get_embedding_size($message->getEmbeddingDriver());

        notify_ui($message->getChat(), 'Searching documents');

        $documentChunkResults = DistanceQueryFacade::cosineDistance(
            embeddingSize: $embeddingSize,
            collectionId: $message->getChatable()->id,
            embedding: $embedding->embedding,
            meta_data: $message->meta_data,
        );

        $content = [];

        /**
         * @NOTE
         * Yes this is a lot like the SearchAndSummarizeChatRepo
         * But just getting a sense of things
         */
        foreach ($documentChunkResults as $result) {
            $contentString = remove_ascii($result->content);
            $content[] = $contentString; //reduce_text_size seem to mess up Claude?
        }

        $context = implode(' ', $content);

        $contentFlattened = SummarizePrompt::prompt(
            originalPrompt: $originalPrompt,
            context: $context
        );

        /**
         * @TODO @WHY
         * Why do I do this system prompt thing?
         */
        $message->getChat()->addInput(
            message: $contentFlattened,
            role: RoleEnum::Assistant,
            systemPrompt: $message->getChat()->getChatable()->systemPrompt(),
            show_in_thread: false,
            meta_data: $message->meta_data,
            tools: $message->tools
        );

        Log::info('[LaraChain] Getting the Search and Summary results', [
            'input' => $contentFlattened,
            'driver' => $message->getChat()->getChatable()->getDriver(),
        ]);

        notify_ui($message->getChat(), 'Building Summary');

        /**
         * @TODO
         * This breaks down here. This was made for a non message / chat
         * based chat since non logged in users could use the chat api.
         */
        Log::info('[LaraChain] Using the Chat Completion', [
            'input' => $contentFlattened,
            'driver' => $message->getChatable()->getDriver(),
        ]);

        $messages = $message->getChat()->getChatResponse();

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $message->getChatable()->getDriver()
        )->setToolType(ToolTypes::NoFunction)
            ->chat($messages);

        $this->response = $response->content;

        $assistantMessage = $message->getChat()->addInput($this->response,
            RoleEnum::Assistant,
            meta_data: $message->meta_data,
            tools: $message->tools);

        PromptHistory::create([
            'prompt' => $contentFlattened,
            'chat_id' => $message->getChat()->id,
            'message_id' => $assistantMessage?->id,
            'collection_id' => $message->getChat()->getChatable()?->id,
        ]);

        $this->saveDocumentReference($assistantMessage, $documentChunkResults);

        notify_ui_complete($message->getChat());

        return FunctionResponse::from(
            [
                'content' => $this->response,
                'save_to_message' => false,
                'prompt' => $contentFlattened,
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
