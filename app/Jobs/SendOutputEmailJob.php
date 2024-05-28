<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Prompts\PromptMerge;
use App\Mail\OutputMail;
use App\Models\Output;
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
        /**
         * @TODO
         * The output model type has to have the
         * ability to build it's own context
         * since Output of type NewsSummary
         * will be different then output of Type EmailReport
         * etc
         * In the end they are driven by the prompt in
         * output->summary
         * but the query for some things (email type)
         * might need more finness
         * So in this case $this->output()->getContext()
         *
         * @see app/Domains/Sources/EmailSource.php:79
         * Find the Class
         * then run the class returning string
         * Right now it is just generic
         */
        $to = $this->output->fromMetaData('to');

        $tos = explode(',', $to);

        $title = $this->output->title;
        $title = 'Summary '.$title;

        $content = $this->output->getContext();

        //put_fixture("latest_content.json", $content);

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

        $tokens = ['[CONTEXT]'];
        $content = [$content];
        $prompt = $this->output->getPrompt();
        $prompt = PromptMerge::merge($tokens, $content, $prompt);

        put_fixture('latest_content_prompt.txt', $prompt, false);

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
