<?php

namespace Tests\Feature\Jobs;

use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Jobs\GetWebContentJob;
use App\Models\Document;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class GetWebContentJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_job(): void
    {
        Bus::fake();

        $document = Document::factory()->create();

        $webResponseDto = WebResponseDto::from([
            'url' => 'https://example.com',
            'title' => 'Example',
            'age' => '1 day',
            'description' => 'Example description',
            'meta_data' => ['key' => 'value'],
            'thumbnail' => 'https://example.com/thumbnail.jpg',
            'profile' => ['key' => 'value'],
        ]);

        GetPage::shouldReceive('make->handle')
            ->once()
            ->andReturn('foobar');

        GetPage::shouldReceive('make->parseHtml')
            ->once()
            ->andReturn('some html');


            [$job, $batch] = (new GetWebContentJob($document, $webResponseDto))->withFakeBatch();
 
            $job->handle();

    }
}
