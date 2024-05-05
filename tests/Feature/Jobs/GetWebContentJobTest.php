<?php

namespace Tests\Feature\Jobs;

use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Jobs\GetWebContentJob;
use App\Models\Document;
use App\Models\Source;
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

        $source = Source::factory()->create();

        $webResponseDto = WebResponseDto::from([
            'url' => 'https://example.com',
            'title' => 'Example',
            'age' => '1 day',
            'description' => 'Example description',
            'meta_data' => ['key' => 'value'],
            'thumbnail' => 'https://example.com/thumbnail.jpg',
            'profile' => ['key' => 'value'],
        ]);

        $content = fake()->sentences(1000, true);

        GetPage::shouldReceive('make->handle')
            ->once()
            ->andReturn('foobar');

        GetPage::shouldReceive('make->parseHtml')
            ->once()
            ->andReturn($content);

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_chunks', 0);
        [$job, $batch] = (new GetWebContentJob($source, $webResponseDto))->withFakeBatch();

        $job->handle();
        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_chunks', 3);

    }
}
