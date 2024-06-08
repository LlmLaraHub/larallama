<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Output;
use App\Models\User;
use Tests\TestCase;

class OutputControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_index(): void
    {
        $collection = Collection::factory()->create();

        Output::factory(10)->create([
            'collection_id' => $collection->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('collections.outputs.index', $collection))
            ->assertOk();
    }

    public function test_delete_no_access(): void
    {
        $output = Output::factory()->create();
        $this->actingAs(User::factory()->create([
            'is_admin' => false,
        ]))
            ->delete(route('collections.outputs.delete', $output))
            ->assertStatus(403);
    }

    public function test_delete_with_access(): void
    {
        $output = Output::factory()->create();
        $user = User::factory()->create([
            'is_admin' => false,
        ]);
        $team = $output->collection->team;
        $user->teams()->attach($team);
        $this->actingAs($user)
            ->delete(route('collections.outputs.delete', $output))
            ->assertRedirectToRoute('collections.outputs.index', $output->collection);

        $this->assertSoftDeleted($output);
    }

    public function test_delete_sending_id(): void
    {
        $output = Output::factory()->create();

        $this->actingAs(User::factory()->create([
            'is_admin' => true,
        ]));

        $response = $this->delete(route('collections.outputs.delete', $output->id));
        $response->assertRedirectToRoute('collections.outputs.index', $output->collection);
    }
}
