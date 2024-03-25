<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

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

        $response = $this->get(route("collections.index"))
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
        $response = $this->post(route("collections.store"), [
            'name' => "Test",
            'description' => 'Test Description',
        ])->assertStatus(302);
        $this->assertDatabaseCount('collections', 1);


    }
}
