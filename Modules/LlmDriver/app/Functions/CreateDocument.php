<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Helpers\ChatHelperTrait;
use App\Models\Document;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class CreateDocument extends FunctionContract
{
    use ChatHelperTrait, ToolsHelper;

    public array $toolTypes = [
        ToolTypes::Source,
        ToolTypes::Chat,
    ];

    protected string $name = 'create_document';

    protected string $description = 'Create a document in the collection of this local system';

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] CreateDocument');

        $args = $message->meta_data->args;

        $content = data_get($args, 'content', null);

        if (! $content) {
            throw new \Exception('No Content Given');
        }

        $document = Document::make(
            $content,
            $message->getChat()->collection
        );

        $document->vectorizeDocument();

        return FunctionResponse::from([
            'content' => $content,
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
                name: 'content',
                description: 'the content to use for the document',
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
