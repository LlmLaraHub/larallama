<?php

namespace App\Jobs;

use App\Domains\EmailParser\MailDto;
use Facades\App\Domains\Sources\EmailSource;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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

            $slug = slug_from_email($this->mailDto->to);
            $source = EmailSource::getSourceFromSlug($slug);

            if (! $source) {
                Log::info('[LaraChain] - Email Source not found', [
                    'slug' => $slug,
                    'to' => $this->mailDto->to,
                ]);
            } else {
                Log::info('[LaraChain] - Email Source found', [
                    'slug' => $slug,
                    'to' => $this->mailDto->to,
                ]);
                EmailSource::setMailDto($this->mailDto)
                    ->handle($source);

            }

        } catch (\Exception $e) {
            logger('[LaraLlama] - Email error', [$e->getMessage()]);

            throw $e;
        }
    }
}
