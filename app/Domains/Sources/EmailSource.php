<?php

namespace App\Domains\Sources;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\PromptMerge;
use App\Jobs\ChunkDocumentJob;
use App\Models\Document;
use App\Models\Source;
use Facades\App\Domains\EmailParser\Client;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class EmailSource extends BaseSource
{
    public ?MailDto $mailDto = null;

    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::EmailSource;

    public static string $description = 'Email Source will use the LaraLamma Assistant Box to get your forwarded messages';

    public function getMailDto(): MailDto
    {
        return $this->mailDto;
    }

    public function setMailDto(MailDto $mailDto): self
    {
        $this->mailDto = $mailDto;

        return $this;
    }

    public function handle(Source $source): void
    {
        if (! $this->mailDto) {
            Client::handle();

            return;
        }

        $this->source = $this->checkForChat($source);

        $this->content = $this->mailDto->getContent();

        $this->documentSubject = $this->mailDto->subject;

        $this->meta_data = $this->mailDto->toArray();

        $prompt = PromptMerge::merge(
            ['[CONTEXT]'],
            [$this->content],
            $source->getPrompt()
        );

        Log::info('[LaraChain] - Running Email Source', [
            'prompt' => $prompt,
        ]);

        $chat = $source->chat;

        $results = LlmDriverFacade::driver(
            $source->getDriver()
        )->completion($prompt);

        if ($this->ifNotActionRequired($results->content)) {
            Log::info('[LaraChain] - Email Source Skipping', [
                'prompt' => $prompt,
            ]);
        } else {

            $userMessage = $chat->addInput(
                message: $prompt,
                role: RoleEnum::User,
                show_in_thread: true,
                meta_data: MetaDataDto::from([
                    'driver' => $source->getDriver(),
                    'source' => $source->title,
                ]),
            );

            $document = Document::updateOrCreate([
                'source_id' => $source->id,
                'type' => TypesEnum::Email,
                'subject' => $this->mailDto->subject,
                'collection_id' => $source->collection_id,
            ], [
                'summary' => $results->content,
                'meta_data' => $this->mailDto->toArray(),
                'original_content' => $this->mailDto->body,
                'status_summary' => StatusEnum::Pending,
                'status' => StatusEnum::Pending,
            ]);

            Bus::batch([new ChunkDocumentJob($document)])
                ->name("Processing Email {$this->mailDto->subject}")
                ->allowFailures()
                ->dispatch();

            $assistantMessage = $chat->addInput(
                message: $results->content,
                role: RoleEnum::Assistant,
                show_in_thread: true,
                meta_data: MetaDataDto::from([
                    'driver' => $source->getDriver(),
                    'source' => $source->title,
                ]),
            );

            $this->savePromptHistory(
                message: $assistantMessage,
                prompt: $prompt);
        }

    }
}
