<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Mail\OutputMail;
use App\Models\Output;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class SendOutputEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Output $output, public bool $testRun = false)
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

        $content = $this->output->getContext();

        if (empty($content)) {
            notify_collection_ui(
                $this->output->collection,
                CollectionStatusEnum::PROCESSED,
                'No new content to send update for'
            );

            return;
        }

        $content = collect($content)
            ->implode("\n");

        $prompt = $this->output->getPrompt();
        $prompt = Templatizer::appendContext(true)
            ->handle($prompt, $content);

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

        if (! $this->testRun) {
            $this->output->updateQuietly([
                'last_run' => now(),
            ]);
        }
    }
}
