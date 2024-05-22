<?php

namespace App\Domains\Sources;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use App\Jobs\GetWebContentJob;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

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

        } catch (\Exception $e) {
            Log::error('[LaraChain] - Error running Email Source', [
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function getSourceFromSlug(string $slug) : Source | null
    {
        $source = Source::where("type", SourceTypeEnum::EmailSource)
            ->slug($slug)
            ->first();

        if($source) {
            return $source;
        }

        return null;
    }
}
