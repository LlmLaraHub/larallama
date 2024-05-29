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
        SourceTypeEnum::EmailBoxSource,
    ];

    public function transform(
        BaseSource $baseSource): BaseSource
    {

        Log::info('[LaraChain] Starting EmailTransformer ', [
            'source' => $baseSource->source->id,
        ]);

        if (! $this->supported($baseSource)) {
            return $baseSource;
        }

        $this->baseSource = $baseSource;

        $mailDto = MailDto::from($baseSource->meta_data);

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
                'summary' => $mailDto->getContent(),
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
        $chunks = $this->documentChunk(
            $document,
            $mailDto->from,
            0,
            0,
            StructuredTypeEnum::EmailFrom
        );
        $this->baseSource->addDocumentChunk($chunks);

        $chunks = $this->documentChunk(
            $document,
            $mailDto->to,
            1,
            0,
            StructuredTypeEnum::EmailTo
        );
        $this->baseSource->addDocumentChunk($chunks);

        $chunks = $this->documentChunk(
            $document,
            $mailDto->subject,
            2,
            0,
            StructuredTypeEnum::EmailFrom
        );
        $this->baseSource->addDocumentChunk($chunks);

        $chunked_content = $this->chunkContent($baseSource->content);

        foreach ($chunked_content as $chunkSection => $chunkContent) {
            $chunks = $this->documentChunk(
                $document,
                $chunkContent,
                3,
                $chunkSection,
                StructuredTypeEnum::EmailBody
            );
            $this->baseSource->addDocumentChunk($chunks);
        }

        /**
         * @NOTE
         * This is key only parent Transformers should do this
         */
        $this->baseSource->setDocument($document);

        return $this->baseSource;
    }
}
