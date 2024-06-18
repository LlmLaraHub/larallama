<?php

namespace App\Domains\Outputs;

use App\Domains\EmailParser\CredentialsDto;
use App\Jobs\EmailReplyOutputJob;
use App\Models\Output;
use Facades\App\Domains\EmailParser\EmailClient;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class EmailReplyOutput extends BaseOutput
{
    public function handle(Output $output): void
    {
        /**
         * So many output types
         * Email
         * API
         * Fax :)
         * Webhooks
         */
        $credentials = CredentialsDto::from($output->secrets);
        $mails = EmailClient::handle($credentials, false);

        $replies = [];

        Log::info('[LaraChain] - EmailReplyOutput', [
            'output' => $output->id,
            'emails_found' => count($mails),
        ]);

        foreach ($mails as $mailDto) {
            $replies[] = new EmailReplyOutputJob($output, $mailDto);
        }

        Bus::batch($replies)
            ->name("Replying to Emails for Output - {$output->id}")
            ->before(function (Batch $batch) {
                // The batch has been created but no jobs have been added...
            })
            ->then(function (Batch $batch) {
            })->catch(function (Batch $batch, \Throwable $e) {
                Log::error('[LaraChain] - Error running Email Reply Output', [
                    'error' => $e->getMessage(),
                    'batch' => $batch->toArray(),
                ]);
            })->finally(function (Batch $batch) {
                //more here
            })
            ->onQueue(
                LlmDriverFacade::driver($output->collection->getDriver())->onQueue()
            )
            ->dispatch();

    }
}
