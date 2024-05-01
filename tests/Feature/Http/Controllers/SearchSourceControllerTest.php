<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\KickOffWebSearchCreationJob;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SearchSourceControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_kicks_off_job(): void
    {
        Queue::fake();

        $user = $this->createUserWithCurrentTeam();

        $collection = Collection::factory()->create([
            'team_id' => $user->currentTeam->id,
        ]);

        $this->assertDatabaseCount('documents', 0);
        $this->actingAs($user)->post(route('search-web.store', [
            'collection' => $collection->id,
            'name' => 'Foo bar',
        ]), [
            'content' => 'This is a text document',
        ])->assertStatus(302);
        $this->assertDatabaseCount('documents', 1);

        Queue::assertPushed(KickOffWebSearchCreationJob::class);
    }
}
