<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Prompts\StandardsCheckerPrompt;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class ReportingTool extends FunctionContract
{
    protected string $name = 'reporting_tool';

    protected string $description = 'Uses Reference collection to generate a report';

    protected string $response = '';

    protected array $results = [];

    protected array $promptHistory = [];

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] ReportingTool Function called');

        $usersInput = MessageInDto::fromMessageAsUser($message);

        $documents = $message->getChatable()->documents;

        notify_ui($message->getChat(), 'Going through all the documents to check standards');

        $this->results = [];

        foreach ($documents->chunk(3) as $index => $chunk) {
            try {

                $prompts = [];

                foreach ($chunk as $document) {
                    if ($document->summary) {
                        /**
                         * @NOTE
                         * This assumes a small amount of incoming content to check
                         * The user my upload a blog post that is 20 paragraphs or more.
                         */
                        $prompt = StandardsCheckerPrompt::prompt(
                            $document->summary, $usersInput->content
                        );
                        $this->promptHistory[] = $prompt;
                        $prompts[] = $prompt;
                    } else {
                        Log::info('[LaraChain] No Summary for Document', [
                            'document' => $document->id,
                        ]);
                    }

                }

                $results = LlmDriverFacade::driver($message->getDriver())
                    ->completionPool($prompts);

                foreach ($results as $result) {
                    $this->results[] = $result->content;
                }
            } catch (\Exception $e) {
                Log::error('Error running Standards Checker', [
                    'error' => $e->getMessage(),
                    'index' => $index,
                ]);
            }
        }

        notify_ui($message->getChat(), 'Wow that was a lot of document!');

        return FunctionResponse::from([
            'content' => implode('\n', $this->results),
            'prompt' => implode('\n', $this->promptHistory),
            'requires_followup' => false,
            'documentChunks' => collect([]),
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
                description: 'The prompt the user is using to use as solutions to the report',
                type: 'string',
                required: true,
            ),
        ];
    }
}
