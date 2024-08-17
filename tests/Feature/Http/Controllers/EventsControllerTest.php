<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Event;
use Tests\TestCase;

class EventsControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_events(): void
    {
        $user = $this->createUserWithCurrentTeam();
        $collection = Collection::factory()->create([
            'team_id' => $user->current_team_id,
        ]);

        Event::factory(10)->create([
            'collection_id' => $collection->id,
        ]);

        $this->actingAs($user)->get(route('collections.events.index', $collection))
            ->assertOk();

    }
}
