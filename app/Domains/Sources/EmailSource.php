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
use Laravel\Prompts\Prompt;
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

        Log::info('[LaraChain] - Running Email Source');

        //use the users source prompt to create this next step
        //track the results in a chat_id thread
        //and as a message?
        //then using the source the results will do something with the content
        // and then delete the email
        // but we can leave it if it returns false?
        // still too much coding there how do I let the tool do it.

        $prompt = PromptMerge::merge(
            ['[CONTEXT]'],
            [$this->content],
            $source->getPrompt()
        );

        $chat = $source->chat;

        $chat->addInput(
            message: $prompt,
            role: RoleEnum::User,
            show_in_thread: true,
            meta_data: MetaDataDto::from([
                'driver' => $source->getDriver(),
                'source' => $source->title,
            ]),
        );

        $results = LlmDriverFacade::driver(
            $source->getDriver()
        )->completion($prompt);

        $chat->addInput(
            message: $results->content,
            role: RoleEnum::Assistant,
            show_in_thread: true,
            meta_data: MetaDataDto::from([
                'driver' => $source->getDriver(),
                'source' => $source->title,
            ]),
        );

        //@TODO how to look for false
        // surface this "power" into the UI.
        // tag or store the fact we checked this emails

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

        //should we delete the email?
        // right now it gets set to seen
        // on the MailDto we have options
    }
}
