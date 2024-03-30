<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\ProcessFileJob;
use App\LlmDriver\DriversEnum;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CollectionControllerTest extends TestCase
{
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
            'embedding_driver' => DriversEnum::Claude->value,
            'description' => 'Test Description',
        ])->assertStatus(302);
        $this->assertDatabaseCount('collections', 1);
        $collection = Collection::first();
        $this->assertEquals(DriversEnum::Claude, $collection->embedding_driver);

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
