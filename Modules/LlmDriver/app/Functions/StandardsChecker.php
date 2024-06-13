<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Prompts\StandardsCheckerPrompt;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class StandardsChecker extends FunctionContract
{
    protected string $name = 'standards_checker';

    protected string $description = 'Checks the prompt data follows the standards of the documents in the collection';

    protected string $response = '';

    protected array $results = [];

    protected array $promptHistory = [];

    public function handle(
        array $messageArray,
        HasDrivers $model,
        FunctionCallDto $functionCallDto): FunctionResponse
    {
        Log::info('[LaraChain] StandardsChecker Function called');

        $usersInput = get_latest_user_content($messageArray);

        $documents = $model->getChatable()->documents;

        notify_ui($model->getChat(), 'Going through all the documents to check standards');

        $this->results = [];

        foreach ($documents->chunk(3) as $index => $chunk) {
            try {

                notify_ui($model->getChat(), sprintf('About to compare document to %d documents in the Collection', count($chunk)));

                $prompts = [];

                foreach ($chunk as $document) {
                    if ($document->summary) {
                        $prompt = StandardsCheckerPrompt::prompt(
                            $document->summary, $usersInput
                        );
                        $title = sprintf('Using Document %s as context', $document->subject);
                        $this->promptHistory[] = StandardsCheckerPrompt::prompt(
                            $title, $usersInput
                        );
                        $prompts[] = $prompt;
                    } else {
                        Log::info('[LaraChain] No Summary for Document', [
                            'document' => $document->id,
                        ]);
                    }

                }

                $results = LlmDriverFacade::driver($model->getDriver())
                    ->completionPool($prompts);

                notify_ui($model->getChat(), 'Got some results will check the next set of documents in the Collection');

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

        notify_ui($model->getChat(), 'Wow that was a lot of document!');

        notify_ui_complete($model->getChat());

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
                description: 'The prompt the user is using to check standards.',
                type: 'string',
                required: true,
            ),
        ];
    }
}
