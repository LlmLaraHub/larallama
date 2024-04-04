<?php

namespace App\LlmDriver\Functions;

use App\Models\Chat;
use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\FunctionResponse;

class SummarizeCollection extends FunctionContract
{
    protected string $name = 'summarize_collection';

    protected string $description = 'This is used when the prompt wants to summarize the entire collection of documents';

    /**
     * 
     * @param MessageInDto[] $messageArray 
     * @param App\LlmDriver\Functions\Chat $chat 
     * @param FunctionCallDto $functionCallDto 
     * @return array 
     */
    public function handle(
        array $messageArray,
        Chat $chat,
        FunctionCallDto $functionCallDto): FunctionResponse
    {
        return FunctionResponse::from([
            'content' => ''
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
                description: 'The prompt the user is using the search for.',
                type: 'string',
                required: true,
            ),
        ];
    }
}
