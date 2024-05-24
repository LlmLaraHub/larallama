<?php

namespace App\Domains\Sources;

use App\Domains\EmailParser\MailDto;
use App\Models\Document;
use App\Models\Source;
use Facades\App\Domains\EmailParser\Client;
use Facades\App\Domains\Transformers\EmailTransformer;
use Illuminate\Support\Facades\Log;

class EmailSource extends BaseSource
{
    public ?MailDto $mailDto = null;

    public SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::EmailSource;

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
            Log::info('Do something!');

            if ($source->transformers()->count() === 0) {

                $transformer = EmailTransformer::transform(baseSource: $this);

                $this->batchTransformedSource($transformer, $source);

            } else {
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
                 */
                Log::info("[LaraChain] - Source has Transformers let's figure out which one to run");
            }

        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running Email Source', [
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function getSourceFromSlug(string $slug): ?Source
    {
        $source = Source::where('type', SourceTypeEnum::EmailSource)
            ->slug($slug)
            ->first();

        if ($source) {
            return $source;
        }

        return null;
    }
}
