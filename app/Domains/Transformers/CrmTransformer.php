<?php

namespace App\Domains\Transformers;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\Prompts\Transformers\GetContactFromEmailPrompt;
use App\Domains\Sources\BaseSource;
use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Transformers\Dtos\ContactDto;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class CrmTransformer extends BaseTransformer
{
    public TypeEnum $type = TypeEnum::CrmTransformer;

    public array $compatible_sources = [
        SourceTypeEnum::EmailSource,
        SourceTypeEnum::EmailBoxSource,
    ];

    public function transform(
        BaseSource $baseSource): BaseSource
    {

        Log::info('[LaraChain] Starting CrmTransformer ', [
            'source' => $baseSource->source->id,
        ]);

        if (! $this->supported($baseSource)) {
            return $baseSource;
        }

        $this->baseSource = $baseSource;

        /**
         * @NOTE2SELF
         * Happy Path
         * The transformer before this was Email
         * The document is here from that output
         * Maybe I can do || document->type === email since it would be the same
         */
        if ($baseSource->document?->type === TypesEnum::Email) {
            $emailDocument = $baseSource->document;
            $mailDto = MailDto::from($emailDocument->meta_data);

            $this->makeTheToContact($mailDto, $emailDocument);

            $this->makeTheFromContact($mailDto, $emailDocument);

        } else {
            //@TODO make a new one for Email?
        }

        return $this->baseSource;
    }

    protected function makeContact(CompletionResponse $contactInfo, Document $emailDocument, StructuredTypeEnum $typeEnum): Document
    {

        $dto = ContactDto::from(json_decode($contactInfo->content, true));
        $socials = $dto->socials;
        $socialsFlat = implode("\n", $socials);

        $summary = <<<CONTENT
First Name: $dto->first_name
Last Name: $dto->last_name,
Company: $dto->company_name,
Email: $dto->email,
Phone: $dto->phone,
Socials:
$socialsFlat
CONTENT;

        $name = $dto->first_name.' '.$dto->last_name;

        if (! $dto->first_name && ! $dto->last_name) {
            $name = $dto->company_name;
        }

        if (is_null($name)) {
            $name = 'Could not find name or company';
        }

        return Document::updateOrCreate(
            [
                'source_id' => $this->baseSource->source->id,
                'type' => TypesEnum::Contact,
                'subject' => $name,
                'collection_id' => $this->baseSource->source->collection_id,
                'parent_id' => $emailDocument->id,
                'child_type' => $typeEnum,
            ],
            [
                'status' => StatusEnum::Complete,
                'file_path' => null,
                'summary' => $summary,
                'status_summary' => StatusEnum::Complete,
                'meta_data' => $dto->toArray(),
            ]
        );

    }

    protected function makeTheToContact(MailDto $mailDto, ?Document $emailDocument): ?Document
    {

        try {
            $contactInfo = $this->sendRequest($mailDto, 'TO');

            $toDocument = $this->makeContact($contactInfo, $emailDocument, StructuredTypeEnum::EmailTo);

            $this->addDocumentChunk($toDocument, StructuredTypeEnum::EmailTo);

            return $toDocument;

        } catch (\Exception $e) {
            Log::info('[LaraChain] - Issue with LLM to Contact response', [
                'message' => $e->getMessage()]);

            return null;
        }
    }

    protected function addDocumentChunk(Document $document, StructuredTypeEnum $type): void
    {

        $document_chunk = DocumentChunk::updateOrCreate([
            'document_id' => $document->id,
            'sort_order' => 0,
            'section_number' => 0,
            'guid' => md5($document->summary),
            'type' => $type,
        ],
            [
                'content' => $document->summary,
            ]);

        $this->baseSource->addDocumentChunk($document_chunk);
    }

    protected function makeTheFromContact(MailDto $mailDto, ?Document $emailDocument): ?Document
    {
        try {
            $contactInfo = $this->sendRequest($mailDto, 'FROM');

            $fromDocument = $this->makeContact($contactInfo, $emailDocument, StructuredTypeEnum::EmailFrom);

            $this->addDocumentChunk($fromDocument, StructuredTypeEnum::EmailFrom);

            return $fromDocument;

        } catch (\Exception $e) {
            Log::info('[LaraChain] - Issue with LLM From Contact response', [
                'message' => $e->getMessage()]);

            return null;
        }
    }

    protected function sendRequest(MailDto $mailDto, string $type = 'TO'): CompletionResponse
    {
        $header = str($mailDto->header)->before('DKIM-Signature')->toString();
        $prompt = GetContactFromEmailPrompt::prompt($mailDto->body, $header, $type);

        return LlmDriverFacade::driver($this->baseSource->source->getDriver())
            ->completion($prompt);
    }
}
