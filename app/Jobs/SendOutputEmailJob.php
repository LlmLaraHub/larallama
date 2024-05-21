<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Prompts\EmailSummaryPrompt;
use App\Mail\OutputMail;
use App\Models\Output;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class SendOutputEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Output $output)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $to = $this->output->fromMetaData('to');

        $tos = explode(',', $to);

        $title = $this->output->title;
        $title = 'Summary '.$title;

        $content = [];

        //get the latest documents to summarize
        $documents = $this->output->collection->documents()->latest()->take(5)->get();
        foreach ($documents as $document) {
            $content[] = $document->content;
        }

        $prompt = EmailSummaryPrompt::prompt(implode("\n", $content), $this->output->summary);

        Log::info('[LaraChain] - Sending this prompt to LLM', [
            'prompt' => $prompt,
        ]);

        notify_collection_ui(
            $this->output->collection,
            CollectionStatusEnum::PROCESSING,
            'Building email message'
        );

        $response = LlmDriverFacade::driver($this->output->collection->getDriver())
            ->completion($prompt);

        notify_collection_ui(
            $this->output->collection,
            CollectionStatusEnum::PROCESSING,
            'Sending email messages'
        );

        foreach ($tos as $to) {
            $to = str($to)->trim()->toString();
            Mail::to($to)->queue(
                new OutputMail(
                    str($response->content)->markdown(),
                    $title
                )
            );
        }

        $this->output->updateQuietly([
            'last_run' => now(),
        ]);

    }
}
