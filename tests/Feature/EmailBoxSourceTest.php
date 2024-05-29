<?php

namespace Tests\Feature;

use App\Domains\EmailParser\EmailClientFacade;
use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Tests\TestCase;
use Webklex\PHPIMAP\Support\FolderCollection;

class EmailBoxSourceTest extends TestCase
{
    public function test_run()
    {

        $body = <<<'BODY'
Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.Quis ea esse velit id id eu consectetur deserunt exercitation exercitation. Nisi aliqua ipsum fugiat laborum aliquip nostrud eu tempor non cillum Lorem non dolor proident sunt. Irure commodo aliqua reprehenderit deserunt sint irure in excepteur quis eiusmod ullamco aliquip. Dolore tempor ea non ut.
BODY;

        $mails = [
            MailDto::from([
                'to' => 'info+12345@llmassistant.io',
                'from' => 'foo@var.com',
                'subject' => 'This is it',
                'header' => 'This is header',
                'body' => $body,
            ]),
            MailDto::from([
                'to' => 'info+12345@llmassistant.io',
                'from' => 'foo@var.com',
                'subject' => 'This is it',
                'header' => 'This is header',
                'body' => $body,
            ]),
        ];

        EmailClientFacade::shouldReceive('setConfig')
            ->once()
            ->andReturnSelf();

        EmailClientFacade::shouldReceive('connect')
            ->once();

        $folders = new FolderCollection();
        EmailClientFacade::shouldReceive('getFolders')
            ->once()
            ->andReturn($folders);

        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::EmailBoxSource,
        ]);

        $source->run();
    }
}
