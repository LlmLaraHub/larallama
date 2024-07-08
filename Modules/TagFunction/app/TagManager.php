<?php

namespace LlmLaraHub\TagFunction;

use App\Domains\Collections\CollectionStatusEnum;
use App\Models\Document;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

        $this->tags = collect(explode(',', $this->tagsAsString));

        $this->tags->take(3)
            ->map(function ($tag) use ($document) {
                $tag = str($tag)
                    ->remove('Here Are 3 Tags:')
                    ->trim()
                    ->toString();
                $document->addTag($tag);
            });

        notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Tags added');
    }
}
