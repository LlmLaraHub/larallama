<?php

namespace App\Domains\Sources;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\Messages\RoleEnum;
use App\Jobs\ChunkDocumentJob;
use App\Models\Document;
use App\Models\Source;
use Facades\App\Domains\EmailParser\Client;
use Facades\App\Domains\Tokenizer\Templatizer;
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

        $assistantMessage = null;

        $this->source = $this->checkForChat($source);

        $key = md5($this->mailDto->date.$this->mailDto->from.$source->id);

        if ($this->skip($this->source, $key)) {
            return;
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

        $results = LlmDriverFacade::driver(
            $source->getDriver()
        )->completion($prompt);

        if ($this->ifNotActionRequired($results->content)) {
            Log::info('[LaraChain] - Email Source Skipping', [
                'prompt' => $prompt,
            ]);
        } else {
            $this->addUserMessage($source, $prompt);

            $promptResultsOriginal = $results->content;
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
                ], [
                    'summary' => $promptResult,
                    'meta_data' => $this->mailDto->toArray(),
                    'original_content' => $this->mailDto->body,
                    'status_summary' => StatusEnum::Pending,
                    'status' => StatusEnum::Pending,
                ]);

                Bus::batch([new ChunkDocumentJob($document)])
                    ->name("Processing Email {$this->mailDto->subject}")
                    ->allowFailures()
                    ->dispatch();

                $assistantMessage = $source->getChat()->addInput(
                    message: $results->content,
                    role: RoleEnum::Assistant,
                    show_in_thread: true,
                    meta_data: MetaDataDto::from([
                        'driver' => $source->getDriver(),
                        'source' => $source->title,
                    ]),
                );
            }

            if ($assistantMessage?->id) {
                $this->savePromptHistory(
                    message: $assistantMessage,
                    prompt: $prompt);
            }

        }

    }
}
