<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\ProcessFileJob;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use LlmLaraHub\LlmDriver\DriversEnum;
use Tests\TestCase;

class CollectionControllerTest extends TestCase
{
    public function test_delete(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $document = Document::factory(4)
            ->has(DocumentChunk::factory(4), 'document_chunks')->create([
                'collection_id' => $collection->id,
            ]);

        $chat = Chat::factory(3)
            ->has(Message::factory(3))->create([
                'chatable_id' => $collection->id,
                'chatable_type' => Collection::class,
            ]);

        $this->actingAs($user)
            ->delete(route('collections.delete', $collection));
        $this->assertDatabaseMissing('collections', [
            'id' => $collection->id,
        ]);
    }

    /**
     * A basic feature test example.
     */
    public function test_index(): void
    {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());

        $collection = Collection::factory()->create([
            'team_id' => $user->currentTeam->id,
        ]);

        Collection::factory()->create();

        $response = $this->get(route('collections.index'))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Collection/Index')
                ->has('collections.data', 1)
            );
    }

    public function test_store(): void
    {
        $user = $this->createUserWithCurrentTeam();
        $this->actingAs($user);

        $this->assertDatabaseCount('collections', 0);
        $response = $this->post(route('collections.store'), [
            'name' => 'Test',
            'driver' => 'mock',
            'summary_prompt' => 'foo bar',
            'embedding_driver' => DriversEnum::Claude->value,
            'description' => 'Test Description',
        ])->assertStatus(302);
        $this->assertDatabaseCount('collections', 1);
        $collection = Collection::first();
        $this->assertEquals(DriversEnum::Claude, $collection->embedding_driver);
        $this->assertEquals('foo bar', $collection->summary_prompt);

    }

    public function test_reindex_document(): void
    {
        Queue::fake();
        $user = $this->createUserWithCurrentTeam();
        $this->actingAs($user);
        $collection = Collection::factory()->create([
            'team_id' => $user->currentTeam->id,
        ]);
        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        DocumentChunk::factory()->create([
            'document_id' => $document->id,
        ]);

        $this->assertCount(1, $document->refresh()->document_chunks);

        $response = $this->post(route('collections.documents.reset', [
            'collection' => $collection->id,
            'document' => $document->id,
        ]))
            ->assertStatus(302);
        $this->assertCount(0, $document->refresh()->document_chunks);
        Queue::assertPushed(ProcessFileJob::class, 1);
    }

    public function test_update(): void
    {
        $user = $this->createUserWithCurrentTeam();
        $this->actingAs($user);
        $collection = Collection::factory()->create([
            'team_id' => $user->currentTeam->id,
        ]);

        $this->assertDatabaseCount('collections', 1);
        $response = $this->put(route('collections.update', $collection), [
            'name' => 'Test',
            'driver' => 'mock',
            'embedding_driver' => DriversEnum::Claude->value,
            'description' => 'Test Description',
        ])->assertStatus(302);
        $this->assertDatabaseCount('collections', 1);

        $this->assertEquals(DriversEnum::Claude, $collection->refresh()->embedding_driver);

    }

    public function test_file_upload()
    {
        Queue::fake();
        Storage::fake('collections');
        $user = $this->createUserWithCurrentTeam();
        $this->actingAs($user);
        $collection = Collection::factory()->create([
            'team_id' => $user->currentTeam->id,
        ]);
        /**
         * @TODO Policy in place by now
         */
        $response = $this->post(route('collections.upload', $collection), [
            'files' => [
                UploadedFile::fake()->create('exaple1.pdf', 1024, 'application/pdf'),
                UploadedFile::fake()->create('exaple1.pdf', 1024, 'application/pdf'),
            ],
        ]);

        Storage::disk('collections')->assertExists("{$collection->id}/exaple1.pdf");

        Queue::assertPushed(ProcessFileJob::class, 2);

    }
}
