<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Source;
use App\Models\User;
use Tests\TestCase;

class SourceControllerTest extends TestCase
{
    public function test_delete(): void
    {
        $source = Source::factory()->create();

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $team = $source->collection->team;

        $user->teams()->attach($team);

        $this->actingAs($user)
            ->delete(route('collections.sources.delete', [
                'source' => $source->id,
            ]))
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertSoftDeleted($source);
    }

    public function test_delete_fail_not_on_team(): void
    {
        $source = Source::factory()->create();

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('collections.sources.delete', [
                'source' => $source->id,
            ]))
            ->assertSessionHasNoErrors()
            ->assertStatus(403);
    }
}
