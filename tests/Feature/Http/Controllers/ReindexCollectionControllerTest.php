<?php

namespace Tests\Feature\Http\Controllers;

use App\Events\CollectionStatusEvent;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReindexCollectionControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_reindex(): void
    {
        Bus::fake();
        Event::fake();
        Storage::fake('collections');
        $user = $this->createUserWithCurrentTeam();
        $this->actingAs($user);
        $collection = Collection::factory()->create([
            'team_id' => $user->currentTeam->id,
        ]);
        Document::factory()
            ->has(DocumentChunk::factory(), 'document_chunks')->create([
            'collection_id' => $collection->id,
        ]);
        /**
         * @TODO Policy in place by now
         */
        $response = $this->post(route('collections.reindex', $collection));

        Event::assertDispatched(CollectionStatusEvent::class);
    }
}
