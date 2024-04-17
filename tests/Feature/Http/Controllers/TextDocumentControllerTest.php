<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class TextDocumentControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create(): void
    {
        Bus::fake();

        $data = get_fixture('chunks.json');

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn(CompletionResponse::from([
                'content' => $data,
            ]));

        $user = $this->createUserWithCurrentTeam();

        $collection = Collection::factory()->create([
            'team_id' => $user->currentTeam->id,
        ]);

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_chunks', 0);
        $this->actingAs($user)->post(route('text-documents.store', [
            'collection' => $collection->id,
            'name' => 'Foo bar',
        ]), [
            'content' => 'This is a text document',
        ])->assertStatus(302);
        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_chunks', 15);
        Bus::assertBatchCount(1);
    }
}
