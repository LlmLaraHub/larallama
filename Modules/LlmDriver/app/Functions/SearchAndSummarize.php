<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Chat;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class SearchAndSummarize extends FunctionContract
{
    protected string $name = 'search_and_summarize';

    protected string $description = 'Used to embed users prompt, search database and return summarized results.';

    public function handle(
        array $messageArray,
        Chat $chat,
        FunctionCallDto $functionCallDto): FunctionResponse
    {
        return FunctionResponse::from(
            [
                'content' => '',
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
