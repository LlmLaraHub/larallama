<?php

namespace App\Domains\Sources;

use App\Domains\EmailParser\MailDto;
use App\Models\Document;
use App\Models\Source;
use App\Models\Transformer;
use Facades\App\Domains\EmailParser\Client;
use Facades\App\Domains\Transformers\EmailTransformer;
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
        if (! $this->mailDto) {
            Client::handle();

            return;
        }

        $this->source = $source;

        $this->content = $this->mailDto->getContent();

        $this->documentSubject = $this->mailDto->subject;

        $this->meta_data = $this->mailDto->toArray();

        $this->transformers = $source->transformers;

        Log::info('[LaraChain] - Running Email Source');

        try {
            $baseSource = EmailTransformer::transform(baseSource: $this);
            /**
             * @NOTE
             * Examples
             * Example One: Maybe there is 1 transformer to make a reply to the email
             * Transformer 1 of 1 ReplyTo Email
             *   Take the email
             *   Use Collection as voice
             *   Make reply to email
             *   The Transformer as an Output attached to it and the reply is sent.
             *
             *  Example Two: CRM Transformer
             *    Take the email and make document (Type Email) and chunks from the email
             *    After that take the content and make who is it to, who is it from
             *    and make Documents for each for those of type Contact
             *    Relate those to the document (Type Email)
             *    and now there are relations for later use
             *
             * @TODO
             * some transformers assume they are never 0 in the chain
             * like CRM assumes the one before was EmailTransformer
             * and the document is set
             */
            Log::info("[LaraChain] - Source has Transformers let's figure out which one to run");

            foreach ($source->transformers as $transformerChainLink) {
                $class = '\\App\\Domains\\Transformers\\'.$transformerChainLink->type->name;
                if (class_exists($class)) {
                    $facade = '\\Facades\\App\\Domains\\Transformers\\'.$transformerChainLink->type->name;
                    $baseSource = $facade::transform($this);
                } else {
                    Log::info('[LaraChain] - No Class found ', [
                        'class' => $class,
                    ]);
                }
            }
            $this->batchTransformedSource($baseSource, $source);

        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running Email Source', [
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function getSourceFromSlug(string $slug): ?Source
    {
        $source = Source::where('type', $this->sourceTypeEnum)
            ->slug($slug)
            ->first();

        if ($source) {
            return $source;
        }

        return null;
    }

    protected function getSummarizeDocumentPrompt(): string
    {
        if (str($this->source->details)->contains('[CONTEXT]')) {
            return $this->source->details;
        }

        return <<<'PROMPT'

The following content is from an email. I would like you to summarize it with the following format.

To: **TO HERE**
From: **From Here**
Subject: **Subject Here**
Body:
**Summary Here**


** CONTEXT IS BELOW THIS LINE**
[CONTEXT]
PROMPT;

    }
}
