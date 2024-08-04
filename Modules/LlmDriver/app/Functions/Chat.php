<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Helpers\ChatHelperTrait;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class Chat extends FunctionContract
{
    use ChatHelperTrait, ToolsHelper;

    public bool $showInUi = false;

    public array $toolTypes = [
        ToolTypes::NoFunction,
    ];

    protected string $name = 'chat_only';

    protected string $description = 'User just wants to continue the chat no need to look in the collection for more documents';

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] Chat');

        $messages = $message->getChat()->getChatResponse();

        $response = LlmDriverFacade::driver($message->getDriver())
            ->setToolType(ToolTypes::NoFunction)
            ->chat($messages);

        return FunctionResponse::from([
            'content' => $response->content,
            'prompt' => $message->getPrompt(),
            'requires_followup' => false,
            'documentChunks' => collect([]),
            'save_to_message' => false,
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
                description: 'the prompt to go with the chat',
                type: 'string',
                required: false,
            ),
        ];
    }

    public function runAsBatch(): bool
    {
        return false;
    }
}
