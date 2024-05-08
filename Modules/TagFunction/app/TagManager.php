<?php

namespace LlmLaraHub\TagFunction;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Collections\CollectionStatusEnum;
use App\Models\Document;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class TagManager
{
    protected Collection $tags;

    public function handle(Document $document): void
    {
        Log::info('[LaraChain] TagManager Tagging document');
        $summary = $document->summary;
        $prompt = <<<EOT
This is the summary of the document, Can you make some tags I can use.
Please return them as a string of text with each tag separated by a comma for example:
Tag 1, Tag Two Test, Tag Three Test

And nothing else. Here is the summary:
### START SUMMARY 
$summary
### END SUMMARY

EOT;
        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver($document->getDriver())
            ->completion(
                prompt: $prompt
            );

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
                'llmResponse' => $response->content,
                'verifyPrompt' => $verifyPrompt,
            ]
        );

        /** @var VerifyPromptOutputDto $response */
        $response = VerifyResponseAgent::verify($dto);

        $this->tags = collect(explode(',', $response->response));

        $this->tags->map(function ($tag) use ($document) {
            $document->addTag($tag);
        });

        foreach ($document->document_chunks as $chunk) {
            $tagsFlat = $this->tags->take(20)->implode(',');
            $summary = $chunk->summary;
            $prompt = <<<EOT
This is one chunk or page number {$chunk->sort_order} in the document , Can you make some tags I can use.
Please return them as a flat string of text with each tag separated by a comma for example:
Tag Foo Bar Example, Tag Example other Test, Tag Example Test
### END EXAMPLE
Return "" if you do not find any content.
Do not prepend or append any text other than the tags.

And nothing else. Here is the summary:
### START SUMMARY 
$summary
### END SUMMARY

### AND HERE ARE EXISTING TAGS
$tagsFlat
### END EXISTING TAGS

EOT;

            /** @var CompletionResponse $response */
            $response = LlmDriverFacade::driver($document->getDriver())
                ->completion(
                    prompt: $prompt
                );

            $verifyPrompt = <<<'PROMPT'
            This was the response from the LLM to get Tags from the content page.
            Please verify the json is good if not fix it so what you return is just JSON
            and remove from tags any text that is not needed and any
            tags that are not correct.
            PROMPT;

            $originalLlm = $response->content;

            $dto = VerifyPromptInputDto::from(
                [
                    'chattable' => $document,
                    'originalPrompt' => $prompt,
                    'context' => $summary,
                    'llmResponse' => $originalLlm,
                    'verifyPrompt' => $verifyPrompt,
                ]
            );

            /** @var VerifyPromptOutputDto $response */
            $response = VerifyResponseAgent::verify($dto);
            Log::info('[LaraChain] TagManager Tagging document VERIFY', [
                'response' => $response->response,
                'original' => $originalLlm,
            ]);

            $tagsChild = explode(',', $response->response);

            foreach ($tagsChild as $tag) {
                $chunk->addTag($tag);
                $this->tags->push($tag);
            }
        }

        notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Tags added');
    }
}
