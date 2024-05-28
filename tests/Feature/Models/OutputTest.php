<?php

namespace Tests\Feature\Models;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Jobs\SendOutputEmailJob;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Output;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OutputTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $output = Output::factory()->create();

        $this->assertNotNull($output->slug);
        $this->assertNotNull($output->meta_data);
        $this->assertEquals(OutputTypeEnum::WebPage, $output->type);
        $this->assertNotNull($output->collection->id);
        $this->assertNotNull($output->collection->outputs()->first()->id);
    }

    public function test_prompt()
    {
        $output = Output::factory()->create([
            'summary' => 'Foobar',
        ]);

        $prompt = $output->getPrompt();

        $shouldBe = <<<'SHOULDBE'
Foobar
***below is the context to use in your summary***
[CONTEXT]
SHOULDBE;

        $this->assertEquals($shouldBe, $prompt);
    }

    public function test_run()
    {
        Queue::fake();

        $collection = Collection::factory()->create();

        $output = Output::factory()->create([
            'type' => OutputTypeEnum::EmailOutput,
            'recurring' => RecurringTypeEnum::HalfHour,
            'collection_id' => $collection->id,
            'last_run' => null,
        ]);

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        DocumentChunk::factory(5)->create(
            ['document_id' => $document->id]
        );

        $output->run();

        Queue::assertPushed(SendOutputEmailJob::class);
    }

    public function test_meta_data()
    {
        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'meta_data' => [
                'to' => 'bob@bobsburgers.com',
            ],
        ]);

        $this->assertEquals('bob@bobsburgers.com',
            $output->fromMetaData('to'));

        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'meta_data' => [],
        ]);

        $this->assertNotNull($output->fromMetaData('to'));
    }
}
