<?php

namespace Tests\Feature\Jobs;

use App\Jobs\VectorlizeDataJob;
use App\Models\DocumentChunk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VectorlizeDataJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_data(): void
    {
        $documentChunk = DocumentChunk::factory()->create([
            'embedding' => null
        ]);

        $job = new VectorlizeDataJob($documentChunk);
        $job->handle();

        $this->assertNotEmpty($documentChunk->embedding);
    }
}
