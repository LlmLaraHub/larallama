<?php

namespace App\Domains\Sources;

use App\Domains\EmailParser\MailDto;
use App\Models\Source;
use Illuminate\Support\Facades\Log;

class EmailSource extends BaseSource
{
    protected MailDto $mailDto;

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
        Log::info('[LaraChain] - Running Email Source');

        try {
            //A Source from email might make
            // 1 Document that is the email
            // 1 Document for the from, to (forwarded)
            //Transformers
            Log::info("Do something!");

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
