<?php

namespace Tests\Feature;

use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Facades\App\Domains\EmailParser\Client;
use Facades\App\Domains\Sources\EmailSource;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class EmailSourceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_slugged_source(): void
    {
        Bus::fake();

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::EmailSource,
        ]);

        $results = EmailSource::getSourceFromSlug('test');

        $this->assertNotNull($results);

        $results = EmailSource::getSourceFromSlug('foobar');

        $this->assertNull($results);
    }

    public function test_batches()
    {
        Bus::fake();
        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::EmailSource,
        ]);

        LlmDriverFacade::shouldReceive('driver->completion')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

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
        $emailSource->setMailDto($dto)->handle($source);

        $this->assertDatabaseCount('documents', 1);

        Bus::assertBatchCount(1);

    }

    public function test_run()
    {
        Client::shouldReceive('handle')->once();

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::EmailSource,
        ]);

        $source->run();
    }

    public function tests_creates_chat_and_message()
    {
        Bus::fake();
        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::EmailSource,
        ]);

        LlmDriverFacade::shouldReceive('driver->completion')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

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
        $emailSource->setMailDto($dto)->handle($source);

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('chats', 1);
        $this->assertDatabaseCount('messages', 2);

        $this->assertNotNull($source->chat_id);

        Bus::assertBatchCount(1);
    }
}
