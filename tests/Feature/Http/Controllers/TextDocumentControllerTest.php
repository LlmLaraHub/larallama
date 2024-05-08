<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class TextDocumentControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create(): void
    {
        Bus::fake();

        $user = $this->createUserWithCurrentTeam();

        $collection = Collection::factory()->create([
            'team_id' => $user->currentTeam->id,
        ]);

        $content = get_fixture('chunkable_text.txt', false);
        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_chunks', 0);
        $this->actingAs($user)->post(route('text-documents.store', [
            'collection' => $collection->id,
            'name' => 'Foo bar',
        ]), [
            'content' => $content,
        ])->assertStatus(302);
        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_chunks', 4);
        Bus::assertBatchCount(1);
    }
}
