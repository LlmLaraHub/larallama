<?php

namespace Tests\Feature;

use App\Domains\EmailParser\CredentialsDto;
use App\Domains\EmailParser\EmailClient;
use App\Domains\EmailParser\EmailClientFacade;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Tests\TestCase;
use Webklex\PHPIMAP\Support\FolderCollection;

class EmailClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_compaitble(): void
    {
        $source = Source::factory()
            ->emailSecrets()
            ->create([
                'slug' => 'test',
                'type' => SourceTypeEnum::EmailBoxSource,
            ]);

        EmailClientFacade::shouldReceive('setConfig')
            ->once()
            ->andReturnSelf();

        EmailClientFacade::shouldReceive('connect')
            ->once();

        $folders = new FolderCollection();
        EmailClientFacade::shouldReceive('getFolders')
            ->once()
            ->andReturn($folders);

        $secrets = CredentialsDto::from($source->secrets);
        (new EmailClient())->handle($secrets);

    }
}
