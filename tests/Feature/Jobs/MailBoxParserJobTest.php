<?php

namespace Tests\Feature\Jobs;

use App\Domains\EmailParser\MailDto;
use App\Domains\Sources\SourceTypeEnum;
use App\Jobs\MailBoxParserJob;
use App\Models\Source;
use App\Models\User;
use Facades\App\Domains\Sources\EmailSource;
use Tests\TestCase;

class MailBoxParserJobTest extends TestCase
{
    public function test_larger_message()
    {
        User::factory()->create();
        EmailSource::shouldReceive('getSourceFromSlug')
            ->once()
            ->andReturn(Source::factory()->create(
                [
                    'type' => SourceTypeEnum::EmailSource,
                    'slug' => '12345',
                ]
            ));
        EmailSource::shouldReceive('setMailDto->handle')->once();
        $dto = MailDto::from([
            'to' => 'info+12345@llmassistant.io',
            'from' => 'foo@var.com',
            'subject' => 'This is it',
            'body' => 'https://foo.com',
        ]);

        $this->assertDatabaseCount('messages', 0);
        [$job, $batch] = (new MailBoxParserJob($dto))->withFakeBatch();

        $job->handle();
    }
}
