<?php

namespace App\Jobs;

use App\Domains\EmailParser\MailDto;
use App\Models\Source;
use Facades\App\Domains\Sources\EmailSource;
use App\Models\LlmFunction;
use App\Models\Message;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MailBoxParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable;

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
            //See if we have an email source
            // and if that slug is part of the email
            // info+slug@laralamma.ai (use config so I can fix this typo)
            $slug = str($this->mailDto->to)->between("+", "@")->toString();

            $source = EmailSource::getSourceFromSlug($slug);

            EmailSource::setMailDto($this->mailDto)
                ->handle($source);


        } catch (\Exception $e) {
            logger('[LaraLlama] - Email error', [$e->getMessage()]);

            throw $e;
        }
    }
}
