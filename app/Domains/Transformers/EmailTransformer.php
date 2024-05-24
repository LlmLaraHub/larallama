<?php

namespace App\Domains\Transformers;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\BaseSource;
use App\Domains\Sources\SourceTypeEnum;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class EmailTransformer extends BaseTransformer
{
    public TypeEnum $type = TypeEnum::EmailTransformer;

    public array $compatible_sources = [
        SourceTypeEnum::EmailSource,
    ];

    public function transform(
        BaseSource $baseSource): self
    {

        /**
         * @NOTE
         * No need to queue this yet since
         * it is not doing any LLM work
         */
        Log::info('[LaraChain] Starting EmailTransformer ', [
            'source' => $baseSource->source->id,
        ]);

        if (! $this->supported($baseSource)) {
            return $this;
        }

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
         * An assumption here
         * I should try to see how to pass in
         * exact objects but
         */
        $mailDto = MailDto::from($baseSource->meta_data);

        $chunks[] = $this->documentChunk(
            $document,
            $mailDto->from,
            0,
            0,
            StructuredTypeEnum::EmailFrom
        );

        $chunks[] = $this->documentChunk(
            $document,
            $mailDto->to,
            1,
            0,
            StructuredTypeEnum::EmailTo
        );

        $chunks[] = $this->documentChunk(
            $document,
            $mailDto->subject,
            2,
            0,
            StructuredTypeEnum::EmailFrom
        );

        $chunks[] = $this->documentChunk(
            $document,
            $mailDto->header,
            3,
            0,
            StructuredTypeEnum::EmailHeader
        );

        $chunked_content = $this->chunkContent($baseSource->content);

        foreach ($chunked_content as $chunkSection => $chunkContent) {
            $chunks[] = $this->documentChunk(
                $document,
                $chunkContent,
                4,
                $chunkSection,
                StructuredTypeEnum::EmailBody
            );
        }

        $this->chunks = $chunks;
        $this->document = $document;

        return $this;
    }
}
