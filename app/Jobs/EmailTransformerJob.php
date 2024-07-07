<?php

namespace App\Jobs;

use App\Domains\EmailParser\MailDto;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Models\Document;
use Facades\App\Domains\Transformers\EmailTransformer;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EmailTransformerJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document)
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

        $document = $this->document;

        $mailDto = MailDto::from($this->document->meta_data);

        $this->batch()->add(
            new VectorlizeDataJob(EmailTransformer::documentChunk(
                $document,
                $mailDto->from,
                0,
                0,
                StructuredTypeEnum::EmailFrom
            ))
        );

        $this->batch()->add(
            new VectorlizeDataJob(EmailTransformer::documentChunk(
                $document,
                $mailDto->to,
                1,
                0,
                StructuredTypeEnum::EmailTo
            ))
        );

        $this->batch()->add(
            new VectorlizeDataJob(EmailTransformer::documentChunk(
                $document,
                $mailDto->subject,
                2,
                0,
                StructuredTypeEnum::EmailSubject
            ))
        );

        $chunked_content = EmailTransformer::chunkContent($mailDto->getContent());

        foreach ($chunked_content as $chunkSection => $chunkContent) {
            $this->batch()->add(
                new VectorlizeDataJob(EmailTransformer::documentChunk(
                    $document,
                    $chunkContent,
                    3,
                    $chunkSection,
                    StructuredTypeEnum::EmailBody
                )));
        }

    }
}
