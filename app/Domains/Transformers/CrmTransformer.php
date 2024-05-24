<?php

namespace App\Domains\Transformers;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Prompts\Transformers\GetContactFromEmailPrompt;
use App\Domains\Sources\BaseSource;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Document;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class CrmTransformer extends BaseTransformer
{
    public TypeEnum $type = TypeEnum::CrmTransformer;

    public array $compatible_sources = [
        SourceTypeEnum::EmailSource,
    ];

    public function transform(
        BaseSource $baseSource): self
    {

        Log::info('[LaraChain] Starting CrmTransformer ', [
            'source' => $baseSource->source->id,
        ]);

        if (! $this->supported($baseSource)) {
            return $this;
        }

        /**
         * @NOTE2SELF
         * Happy Path
         * The transformer before this was Email
         * The document is here from that output
         */
        if ($baseSource->lastRan?->type === TypesEnum::Email) {
            $emailDocument = $baseSource->document;

            //GET THE TO in case forwarded
            $prompt = GetContactFromEmailPrompt::prompt($emailDocument);
            $contactInfo = LlmDriverFacade::driver($baseSource->source->getDriver())
                ->completion($prompt);

            //            $documentTo = Document::updateOrCreate(
            //                [
            //                    'source_id' => $baseSource->source->id,
            //                    'type' => TypesEnum::Contact,
            //                    'subject' => $baseSource->documentSubject,
            //                    'collection_id' => $baseSource->source->collection_id,
            //                ],
            //                [
            //                    'status' => StatusEnum::Pending,
            //                    'file_path' => null,
            //                    'status_summary' => StatusEnum::Pending,
            //                    'meta_data' => $baseSource->meta_data,
            //                ]
            //            );

        } else {
            //@TODO make a new one for Email?
        }

        return $this;
    }
}
