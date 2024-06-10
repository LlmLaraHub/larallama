<?php

namespace Tests\Feature;

use App\Jobs\ProcessTextFilesJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProcessTextFilesJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_markdown(): void
    {
        $this->markTestSkipped('@TODO better mocking on ci');

        Bus::fake();

        $collection = \App\Models\Collection::factory()->create();

        $document = \App\Models\Document::factory()->create([
            'summary' => '',
            'type' => \App\Domains\Documents\TypesEnum::Txt,
            'collection_id' => $collection->id,
            'file_path' => 'foobar.txt',
        ]);

        $text = get_fixture('example_markdown.md', false);

        if (! File::exists($document->pathToFile())) {
            File::put($document->pathToFile(), $text);
        }

        [$job, $batch] = (new ProcessTextFilesJob($document))->withFakeBatch();

        $job->handle();

        $this->assertDatabaseCount('document_chunks', 2);

        $this->assertEquals($text, $document->refresh()->summary);
        Bus::assertBatchCount(1);
    }
}
