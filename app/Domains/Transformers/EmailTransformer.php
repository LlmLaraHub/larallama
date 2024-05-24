<?php

namespace App\Domains\Transformers;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\BaseSource;
use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Transformers\BaseTransformer;
use App\Helpers\TextChunker;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Source;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;

class EmailTransformer extends BaseTransformer
{
    public TypeEnum $type = TypeEnum::EmailTransformer;


    public function transform(
            BaseSource $baseSource) : self {
        /**
         * @NOTE
         * No need to queue this yet since
         * it is not doing any LLM work
         */
        Log::info('[LaraChain] Starting EmailTransformer ', [
            'source' => $baseSource->source->id,
        ]);

        $chunks = [];

        $document = Document::updateOrCreate(
            [
                'source_id' => $baseSource->source->id,
                'type' => TypesEnum::Email,
                'subject' => $baseSource->documentSubject,
                'collection_id' => $baseSource->source->collection_id,
            ],
            [
                'status' => StatusEnum::Pending,
                'file_path' => null,
                'status_summary' => StatusEnum::Pending,
                'meta_data' => $baseSource->meta_data,
            ]
        );

        /**
         * @NOTE
         * bit of an assumption here
         * I should try to see how to pass in
         * exact objects but
         */
        $mailDto = MailDto::from($baseSource->meta_data);

        $chunks[] = $this->documentChunk(
            $document,
            $mailDto->from,
            0,
            0
        );

        $chunks[] = $this->documentChunk(
            $document,
            $mailDto->to,
            1,
            0
        );

        $chunks[] = $this->documentChunk(
            $document,
            $mailDto->subject,
            2,
            0
        );

        $chunks[] = $this->documentChunk(
            $document,
            $mailDto->header,
            3,
            0
        );

        $chunked_content = $this->chunkContent($baseSource->content);

        foreach ($chunked_content as $chunkSection => $chunkContent) {
            $chunks[] = $this->documentChunk(
                $document,
                $chunkContent,
                4,
                $chunkSection
            );
        }

        $this->chunks = $chunks;
        $this->document = $document;


        return $this;
    }



}
