<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Messages\RoleEnum;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use App\Models\Chat;
use App\Models\DocumentChunk;
use Illuminate\Contracts\Container\BindingResolutionException;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Spatie\LaravelData\Exceptions\MaxTransformationDepthReached;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Exceptions\CannotSetComputedValue;

class SearchAndSummarize extends FunctionContract
{
    protected string $name = 'search_and_summarize';

    protected string $description = 'Used to embed users prompt, search database and return summarized results.';

    /**
     * 
     * @param MessageInDto[] $messageArray 
     * @param HasDrivers $model 
     * @param FunctionCallDto $functionCallDto 
     * @return FunctionResponse 
     */
    public function handle(
        array $messageArray,
        HasDrivers $model,
        FunctionCallDto $functionCallDto): FunctionResponse
    {
        /**
         * @TODO
         *
         * @see https://github.com/orgs/LlmLaraHub/projects/1/views/1?pane=issue&itemId=59671259
         * 
         * @TODO 
         * Should I break up the string using the LLM to make the search better?
         * 
         */
        $input = collect($messageArray)->first(function($item) {
            return $item->role === 'user';
        });

        $input = $input->content;

        /** @var EmbeddingsResponseDto $embedding */
        $embedding = LlmDriverFacade::driver(
            $model->getEmbeddingDriver()
        )->embedData($input);

        $embeddingSize = get_embedding_size($model->getEmbeddingDriver());


        /**
         * @TODO 
         * Track the document page for referehce
         * @see https://github.com/orgs/LlmLaraHub/projects/1?pane=issue&itemId=60394288
         */
        $results = DocumentChunk::query()
            ->join('documents', 'documents.id', '=', 'document_chunks.document_id')
            ->selectRaw(
                "document_chunks.{$embeddingSize} <-> ? as distance, document_chunks.content, document_chunks.{$embeddingSize} as embedding, document_chunks.id as id",
                [$embedding->embedding]
            )
            ->where('documents.collection_id', $model->getChatable()->id)
            ->limit(10)
            ->orderByRaw('distance')
            ->get();

        $content = [];

        /**
         * @NOTE
         * Yes this is a lot like the SearchAndSummarizeChatRepo
         * But just getting a sense of things
         */
        foreach ($results as $result) {
            $contentString = remove_ascii($result->content);
            if (Feature::active('reduce_text')) {
                $result = reduce_text_size($contentString);
            }
            $content[] = $contentString; //reduce_text_size seem to mess up Claude?
        }

        $content = implode(' ', $content);

        $content = "This is data from the search results when entering the users prompt which is ### START PROMPT ### {$input} ### END PROMPT ###  please use this with the following context and only this, summarize it for the user and return as markdown so I can render it and strip out and formatting like extra spaces, tabs, periods etc: ".$content;

        $model->getChat()->addInput(
            message: $content,
            role: RoleEnum::Assistant,
            systemPrompt: $model->getChat()->chatable->systemPrompt(),
            show_in_thread: false
        );

        Log::info('[LaraChain] Getting the Summary from the search results');

        $messageArray = MessageInDto::from([
            'content' => $content,
            'role' => 'user',
        ]);

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $model->getChatable()->getDriver()
        )->chat([$messageArray]);

        $model->getChat()->addInput($response->content, RoleEnum::Assistant);

        return FunctionResponse::from(
            [
                'content' => $content,
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
