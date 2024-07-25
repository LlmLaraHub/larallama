<?php

namespace Feature;

use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Message;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use LlmLaraHub\LlmDriver\Functions\GatherInfoTool;
use Tests\TestCase;

class GatherInfoToolTest extends TestCase
{
    public function test_asks()
    {
        Queue::fake();

        Bus::fake();

        $collection = Collection::factory()->create();

        Document::factory(9)
            ->has(DocumentChunk::factory(), 'document_chunks')
            ->create([
                'collection_id' => $collection->id,
            ]);

        $chat = \App\Models\Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
        ]);

        $this->assertDatabaseCount('sections', 0);

        $results = (new GatherInfoTool())
            ->handle($message);

        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->jobs->count() === 3;
        });
    }
}
