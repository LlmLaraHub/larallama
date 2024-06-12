<?php

namespace LlmLaraHub\TagFunction;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Collections\CollectionStatusEnum;
use App\Models\Document;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class TagManager
{
    protected Collection $tags;

    protected string $tagsAsString = '';

    public function handle(Document $document): void
    {
        if (! $document->summary) {
            return;
        }

        Log::info('[LaraChain] TagManager Tagging document');
        $summary = $document->summary;
        $prompt = TagPrompt::prompt($summary);

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver($document->getDriver())
            ->completion(
                prompt: $prompt
            );

        $this->tagsAsString = $response->content;

        if (Feature::active('verification_prompt_tags')) {
            $verifyPrompt = <<<'PROMPT'
            This was the response from the LLM to get Tags from the content.
            Please verify the json is good if not fix it so what you return is just JSON
            and remove from tags any text that is not needed and any
            tags that are not correct.
            PROMPT;

            $dto = VerifyPromptInputDto::from(
                [
                    'chattable' => $document,
                    'originalPrompt' => $prompt,
                    'context' => $summary,
                    'llmResponse' => $this->tagsAsString,
                    'verifyPrompt' => $verifyPrompt,
                ]
            );

            /** @var VerifyPromptOutputDto $response */
            $response = VerifyResponseAgent::verify($dto);

            $this->tagsAsString = $response->response;

        }

        $this->tags = collect(explode(',', $this->tagsAsString));

        $this->tags->take(3)
            ->map(function ($tag) use ($document) {
                $document->addTag($tag);
            });

        notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Tags added');
    }
}
