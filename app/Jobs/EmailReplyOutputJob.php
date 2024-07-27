<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\Prompts\PromptMerge;
use App\Mail\OutputMail;
use App\Models\Output;
use App\Models\Persona;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class EmailReplyOutputJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public Output $output, public MailDto $mailDto)
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

        $userInput = $this->mailDto->getContent();

        //get the results for the email
        $prompt = $this->output->summary;

        /**
         * @NOTE
         * Not ideal to assume that the users email will return
         * a decent vector result.
         * So after this I will do the summary and then use t
         * that as context like I did with Standards Checker
         */
        $embedding = LlmDriverFacade::driver(
            $this->output->collection->getEmbeddingDriver()
        )->embedData($userInput);

        $embeddingSize = get_embedding_size(
            $this->output->collection->getEmbeddingDriver());

        /** @phpstan-ignore-next-line */
        $documentChunkResults = DistanceQueryFacade::cosineDistance(
            $embeddingSize,
            $this->output->collection->id, //darn I do not have this!
            $embedding->embedding,
            null,
        );

        /**
         * @TDDO
         * for now I will summarize
         * then I will send all of these to see what is
         * best using the completionPool
         */
        $content = [];

        /**
         * @NOTE
         * Yes this is a lot like the SearchAndSummarizeChatRepo
         * But just getting a sense of things
         */
        foreach ($documentChunkResults as $result) {
            $contentString = remove_ascii($result->content);
            $content[] = $contentString; //reduce_text_size seem to mess up Claude?
        }

        if (count($content) > 0) {
            $context = implode(' ', $content);

            if ($this->output->persona_id) {
                $persona = Persona::find($this->output->persona_id);
                $context = $persona->wrapPromptInPersona($userInput);
            }

            $tokens = [
                '[CONTEXT]',
                '[USER_INPUT]',
            ];

            $prompt = PromptMerge::merge($tokens, [
                $context,
                $userInput,
            ], $prompt);

            Log::info('[LaraChain] - Sending Prompt to LLM');

            $results = LlmDriverFacade::driver($this->output->collection->getDriver())
                ->completion($prompt);

            Log::info('[LaraChain] - Email Reply Output from LLM', [
                'results' => $results->content,
            ]);

            $documentSubject = $this->mailDto->subject;

            $signature = data_get($this->output->meta_data, 'signature', '');

            $reply = str($results->content)
                ->append("\n\n")
                ->append($signature)
                ->markdown();

            $from = $this->mailDto->from;
            $from = clean_email($from);
            Mail::to($from)
                ->send(
                    new OutputMail(
                        $reply,
                        "Re: $documentSubject"
                    )
                );

            notify_collection_ui(
                $this->output->collection,
                CollectionStatusEnum::PROCESSED,
                'Email Reply Output Complete');
        } else {
            Log::info('[LaraChain] - No results found for email reply', [
                'output' => $this->output->id,
            ]);

        }

    }
}
