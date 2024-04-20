<?php

namespace App\Listeners;

use App\Events\DocumentParsedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class TagDocumentListener implements ShouldQueue
{
    protected Collection $tags;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DocumentParsedEvent $event): void
    {
        Log::info('[LaraChain] Tagging document');

        $document = $event->document;
        $summary = $document->summary;
        $prompt = <<<EOT
This is the summary of the document, Can you make some tags I can use.
Please return them as a string of text with each tag separated by a comma for example:
Tag 1, Tag Two Test, Tag Three Test

And nothing else. Here is the summary:
### START SUMMARY 
{$summary}
### END SUMMARY

EOT;
        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver($document->getDriver())
            ->completion(
                prompt: $prompt
            );

        $this->tags = collect(explode(',', $response->content));

        Log::info('[LaraChain] Tags Found: '.$response->content);

        $this->tags->map(function ($tag) use ($document) {
            $document->addTag($tag);
        });

        foreach ($document->document_chunks as $chunk) {
            $tagsFlat = $this->tags->implode(',');
            $summary = $chunk->summary;
            $prompt = <<<EOT
            This is one chunk or page number {$chunk->sort_order} in the document , Can you make some tags I can use.
            Please return them as a string of text with each tag separated by a comma for example:
            Tag 1, Tag Two Test, Tag Three Test
            
            And nothing else. Here is the summary:
            ### START SUMMARY 
            {$summary}
            ### END SUMMARY
            
            ### AND HERE ARE EXISTING TAGS
            {$tagsFlat}
            ### END EXISTING TAGS

            EOT;

            /** @var CompletionResponse $response */
            $response = LlmDriverFacade::driver($document->getDriver())
                ->completion(
                    prompt: $prompt
                );

            $tagsChild = explode(',', $response->content);

            foreach ($tagsChild as $tag) {
                $chunk->addTag($tag);
                $this->tags->push($tag);
            }
        }

    }
}
