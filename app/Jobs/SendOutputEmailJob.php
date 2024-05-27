<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Prompts\PromptMerge;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Mail\OutputMail;
use App\Models\Document;
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

        //get the latest documents to summarize
        $documents = $this->output->collection
            ->documents()
            ->where('type', TypesEnum::Email)
            ->when($this->output->last_run != null, function ($query) {
                $query->whereDate('created_at', '>=', $this->output->last_run);
            })
            ->latest()
            ->get();

        if (empty($documents)) {
            Log::info('LaraChain] - No Emails since the last run');

            return;
        }
        $content = [];

        foreach ($documents as $document) {
            if (! empty($document->children)) {
                foreach ($document->children as $child) {
                    $content[] = $this->getContentFromChild($child);
                }
            } else {
                //@TODO
                // we get it from the chunks that are to and from
                //and the summary
            }
            $content[] = 'Sent At: '.$document->created_at;
            $content[] = 'Subject: '.$document->subject;

            $content[] = "### START BODY\n";
            $content[] = $this->getEmailSummary($document);
            $content[] = "### END BODY\n";

        }

        $content = implode("\n", $content);
        $tokens = ['[CONTEXT]'];
        $content = [$content];

        $prompt = PromptMerge::merge($tokens, $content, $this->output->summary);

        Log::info('[LaraChain] - Sending this prompt to LLM', [
            'prompt' => $prompt,
        ]);

        put_fixture('prompt_for_summary_report.txt', $prompt, false);

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

    protected function getContentFromChild(Document $document): string
    {
        $type = ($document->child_type === StructuredTypeEnum::EmailTo) ? 'To' : 'From';
        $summary = $document->summary;

        $message = <<<MESSAGE
This email was $type the following Contact
$summary
MESSAGE;

        return $message;
    }

    protected function getEmailSummary(Document $document): string
    {
        /** @phpstan-ignore-next-line */
        $content = $document
            ->document_chunks()
            ->where('type', StructuredTypeEnum::EmailBody)
            ->orderBy('section_number')
            ->get()
            ->pluck('content')
            ->implode("\n");

        return $content;
    }
}
