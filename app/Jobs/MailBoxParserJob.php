<?php

namespace App\Jobs;

use App\Domains\EmailParser\MailDto;
use App\Models\Source;
use Facades\App\Domains\Sources\EmailSource;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MailBoxParserJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public MailDto $mailDto)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...

            return;
        }

        try {

            $slug = str($this->mailDto->to)->between('+', '@')->toString();

            $source = EmailSource::getSourceFromSlug($slug);

            EmailSource::setMailDto($this->mailDto)
                ->handle($source);

        } catch (\Exception $e) {
            logger('[LaraLlama] - Email error', [$e->getMessage()]);

            throw $e;
        }
    }
}
