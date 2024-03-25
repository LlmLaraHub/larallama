<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CollectionControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
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
            'description' => 'Test Description',
        ])->assertStatus(302);
        $this->assertDatabaseCount('collections', 1);

    }
}
