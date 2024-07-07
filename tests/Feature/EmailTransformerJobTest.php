<?php

namespace Tests\Feature;

use App\Domains\UnStructured\StructuredTypeEnum;
use App\Jobs\EmailTransformerJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EmailTransformerJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_reset()
    {
        Bus::fake();
        $meta_data = get_fixture('email_meta_data.json');
        $document = Document::factory()->create([
            'meta_data' => $meta_data,
        ]);

        $this->assertDatabaseCount('document_chunks', 0);
        [$job, $batch] = (new EmailTransformerJob($document))->withFakeBatch();
        $job->handle();

        $this->assertDatabaseCount('document_chunks', 6);

        $chunk = DocumentChunk::where('document_id', $document->id)
            ->whereType(StructuredTypeEnum::EmailBody)->first();

        $this->assertStringContainsString('What impressed us most', $chunk->content);

    }
}
