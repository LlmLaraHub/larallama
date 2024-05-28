<?php

namespace Tests\Feature;

use App\Domains\Outputs\EmailOutput;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Output;
use Tests\TestCase;

class EmailOutputTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_content(): void
    {
        $collection = Collection::factory()->create();

        $output = Output::factory()->create([
            'collection_id' => $collection->id,
            'last_run' => null,
        ]);

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        DocumentChunk::factory(5)->create(
            ['document_id' => $document->id]
        );

        $results = (new EmailOutput())->getContext($output);

        $this->assertCount(5, $results);
    }
}
