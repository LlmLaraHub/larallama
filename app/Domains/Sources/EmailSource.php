<?php

namespace App\Domains\Sources;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\EmailParser\MailDto;
use App\Jobs\ChunkDocumentJob;
use App\Models\Document;
use App\Models\Message;
use App\Models\Source;
use Facades\App\Domains\EmailParser\Client;
use Facades\App\Domains\Orchestration\OrchestrateVersionTwo;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

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
        $mailsComingIn = Client::getEmails($source->slug);
        $this->source = $this->checkForChat($source);

        if (empty($mailsComingIn)) {
            Log::info('No mails coming in Source '.$source->id);

            return;
        } else {
            foreach ($mailsComingIn as $mailDto) {
                $this->mailDto = $mailDto;

                $key = md5($this->mailDto->getContent());

                if ($this->skip($this->source, $key)) {
                    $this->mailDto->email_message?->delete();

                    continue;
                }

                $this->createSourceTask($this->source, $key);

                $this->content = $this->mailDto->getContent();

                $this->documentSubject = $this->mailDto->subject;

                $this->meta_data = $this->mailDto->toArray();

                $prompt = Templatizer::appendContext(true)
                    ->handle($source->getPrompt(), $this->content);

                Log::info('[LaraChain] - Running Email Source', [
                    'prompt' => $prompt,
                ]);

                /** @var Message $assistantMessage */
                $assistantMessage = OrchestrateVersionTwo::sourceOrchestrate(
                    $source->refresh()->chat,
                    $prompt
                );

                if ($this->ifNotActionRequired($assistantMessage->getContent())) {
                    Log::info('[LaraChain] - Email Source Skipping', [
                        'prompt' => $prompt,
                    ]);
                } else {
                    Log::info('[LaraChain] - Email Source Results from Orchestrate', [
                        'assistant_message' => $assistantMessage->id,
                    ]);
                    $promptResultsOriginal = $assistantMessage->getContent();
                    $promptResults = $this->arrifyPromptResults($promptResultsOriginal);
                    foreach ($promptResults as $promptResultIndex => $promptResult) {
                        $promptResult = json_encode($promptResult);

                        $title = sprintf('Email Subject - item #%d -%s',
                            $promptResultIndex + 1,
                            $this->mailDto->subject);

                        $document = Document::updateOrCreate([
                            'source_id' => $source->id,
                            'type' => TypesEnum::Email,
                            'subject' => $title,
                            'collection_id' => $source->collection_id,
                            'original_content' => $this->content,
                        ], [
                            'summary' => $promptResult,
                            'meta_data' => $this->mailDto->toArray(),
                            'status_summary' => StatusEnum::Pending,
                            'status' => StatusEnum::Pending,
                        ]);

                        Bus::batch([new ChunkDocumentJob($document)])
                            ->name("Processing Email {$this->mailDto->subject}")
                            ->allowFailures()
                            ->dispatch();

                        $this->mailDto->email_message?->delete();
                    }

                }
            }
        }

    }
}
