<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class SummarizeCollection extends FunctionContract
{
    protected string $name = 'summarize_collection';

    protected string $description = 'NOT FOR SEARCH, This is used when the prompt wants to summarize the entire collection of documents';

    public function handle(
        array $messageArray,
        HasDrivers $model,
        FunctionCallDto $functionCallDto): FunctionResponse
    {
        Log::info('[LaraChain] SummarizeCollection function called');

        $summary = collect([]);

        foreach ($model->chatable->documents as $document) {
            foreach ($document->document_chunks as $chunk) {
                $summary->add($chunk->summary);
            }
        }

        $summary = $summary->implode('\n');

        $prompt = 'Can you summarize all of this content for me from a collection of documents I uploaded what follows is the content: '.$summary;

        $messagesArray = [];

        $messagesArray[] = MessageInDto::from([
            'content' => $prompt,
            'role' => 'user',
        ]);

        $results = LlmDriverFacade::driver($model->getDriver())->chat($messagesArray);



        return FunctionResponse::from([
            'content' => $results->content,
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
