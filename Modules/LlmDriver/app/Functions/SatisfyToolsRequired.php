<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Helpers\ChatHelperTrait;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class SatisfyToolsRequired extends FunctionContract
{
    use ChatHelperTrait, ToolsHelper;

    public bool $showInUi = false;

    public array $toolTypes = [
        ToolTypes::NoFunction,
    ];

    protected string $name = 'satisfy_tools_required';

    protected string $description = 'This tool has no use just for example purposes';

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] SatisfyToolsRequired');

        return FunctionResponse::from([
            'content' => 'Should not be called',
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
                name: 'example_arg',
                description: 'Example argument',
                type: 'string',
                required: true,
            ),
        ];
    }

    public function runAsBatch(): bool
    {
        return false;
    }
}
