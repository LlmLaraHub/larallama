<?php

namespace Tests\Feature;

use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\BaseSource;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Document;
use App\Models\Source;
use Facades\App\Domains\Transformers\EmailTransformer;
use Tests\TestCase;

class EmailTransformerTest extends TestCase
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

        $body = <<<'BODY'
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.

BODY;

        $dto = MailDto::from([
            'to' => 'info+12345@llmassistant.io',
            'from' => 'foo@var.com',
            'subject' => 'This is it',
            'header' => 'This is header',
            'body' => $body,
        ]);

        $emailSource = new \App\Domains\Sources\EmailSource();
        $emailSource->source = $source;
        $emailSource->mailDto = $dto;
        $emailSource->documentSubject = 'Foobar';
        $emailSource->content = $dto->getContent();
        $emailSource->meta_data = $dto->toArray();

        $transformer = EmailTransformer::transform($emailSource);

        $this->assertDatabaseCount('documents', 1);

        $document = Document::first();
        $this->assertEquals($dto->getContent(), $document->summary);

        $this->assertDatabaseCount('document_chunks', 8);

        $this->assertInstanceOf(BaseSource::class, $transformer);

        $this->assertCount(8, $transformer->document_chunks);

    }
}
