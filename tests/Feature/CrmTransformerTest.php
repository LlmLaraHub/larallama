<?php

namespace Tests\Feature;

use App\Domains\Documents\TypesEnum;
use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Transformers\CrmTransformer;
use App\Domains\Transformers\TypeEnum;
use App\Models\Document;
use App\Models\Source;
use App\Models\Transformer;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class CrmTransformerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_transformer(): void
    {

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::EmailSource,
        ]);

        $contactInfo = get_fixture('contact_info.json', false);
        $contactFromInfo = get_fixture('contact_info_from.json', false);

        LlmDriverFacade::shouldReceive('driver->completion')
            ->twice()
            ->andReturns(
                CompletionResponse::from(['content' => $contactInfo]),
                CompletionResponse::from(['content' => $contactFromInfo])
            );

        $transformerModel = Transformer::factory()->create([
            'type' => TypeEnum::EmailTransformer,
        ]);

        $document = Document::factory()->email()->create([
            'type' => TypesEnum::Email,
        ]);

        $emailSource = new \App\Domains\Sources\EmailSource();

        $this->assertDatabaseCount('documents', 1);

        $emailSource
            ->setDocument($document)
            ->setSource($source)
            ->setLastRun($transformerModel) //not needed but hmm
            ->setMailDto(MailDto::from($document->meta_data));

        $transformer = new CrmTransformer();
        $results = $transformer->transform($emailSource);

        $this->assertDatabaseCount('documents', 3);
        $this->assertCount(2, $results->document_chunks);
    }

    public function test_missing_names(): void
    {

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::EmailSource,
        ]);

        $contactInfo = get_fixture('contact_info_company.json', false);
        $contactFromInfo = get_fixture('contact_info_from.json', false);

        LlmDriverFacade::shouldReceive('driver->completion')
            ->twice()
            ->andReturns(
                CompletionResponse::from(['content' => $contactInfo]),
                CompletionResponse::from(['content' => $contactFromInfo])
            );

        $transformerModel = Transformer::factory()->create([
            'type' => TypeEnum::EmailTransformer,
        ]);

        $document = Document::factory()->email()->create([
            'type' => TypesEnum::Email,
        ]);

        $emailSource = new \App\Domains\Sources\EmailSource();

        $this->assertDatabaseCount('documents', 1);

        $emailSource
            ->setDocument($document)
            ->setSource($source)
            ->setLastRun($transformerModel) //not needed but hmm
            ->setMailDto(MailDto::from($document->meta_data));

        $transformer = new CrmTransformer();
        $results = $transformer->transform($emailSource);

        $this->assertDatabaseCount('documents', 3);

        $this->assertTrue(
            Document::whereSubject('Bobs Burgers')->exists()
        );
    }
}
