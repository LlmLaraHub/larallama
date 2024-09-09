<?php

namespace Tests\Feature\Http\Controllers;

use App\Domains\Projects\StatusEnum;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use LlmLaraHub\LlmDriver\DriversEnum;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_index(): void
    {
        $user = User::factory()->create();

        $team = Team::factory()->create([
            'user_id' => $user->id,
        ]);

        $team->users()->attach($user, ['role' => 'admin']);

        $user->current_team_id = $team->id;
        $user->updateQuietly();

        Project::factory(3)->create([
            'team_id' => $team->id,
        ]);

        $teamNot = Team::factory()->create();
        Project::factory()->create([
            'team_id' => $teamNot->id,
        ]);

        $this->actingAs($user)->get(
            route('projects.index')
        )->assertStatus(200)
            ->assertInertia(fn (Assert $assert) => $assert
                ->has('projects.data', 3)
            );
    }

    public function test_create(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(
            route('projects.create')
        )->assertStatus(200)
            ->assertInertia(fn (Assert $assert) => $assert
                ->has('statuses')
            );
    }

    public function test_store(): void
    {
        $user = User::factory()->create();

        $team = Team::factory()->create([
            'user_id' => $user->id,
        ]);

        $user->current_team_id = $team->id;
        $user->updateQuietly();

        $this->actingAs($user)->post(
            route('projects.store'), [
                'name' => 'Test Campaign',
                'start_date' => '2023-01-01',
                'end_date' => '2023-01-01',
                'content' => 'Test Description',
                'system_prompt' => 'Test Description',
                'chat_driver' => DriversEnum::Claude->value,
                'embedding_driver' => DriversEnum::Claude->value,
                'status' => StatusEnum::Draft->value,
            ]
        )
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $campaign = Project::first();
        $this->assertNotNull($campaign->team_id);

        $this->assertNotEmpty($campaign->chats()->first()?->id);

    }

    public function test_show(): void
    {
        $user = User::factory()->create();

        $campaign = Project::factory()->create();

        $this->assertDatabaseCount('chats', 0);

        $this->actingAs($user)->get(
            route('projects.show', $campaign)
        )->assertStatus(200)
            ->assertInertia(fn (Assert $assert) => $assert
                ->has('project.data')
            );

        $this->assertDatabaseCount('chats', 1);
    }

    public function test_edit(): void
    {
        $user = User::factory()->create();

        $campaign = Project::factory()->create();

        $this->actingAs($user)->get(
            route('projects.edit', $campaign)
        )->assertStatus(200)
            ->assertInertia(fn (Assert $assert) => $assert
                ->has('project.data')
            );
    }

    public function test_update(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create();

        $this->actingAs($user)->put(
            route('projects.update', $project), [
                "name" => "Test Campaign 2",
                'start_date' => '2023-01-01',
                'system_prompt' => 'Test Description',
                'end_date' => '2023-01-01',
                'content' => 'Test Description',
                'status' => StatusEnum::Draft->value,
            ]
        )->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertEquals('Test Campaign 2', $project->refresh()->name);
    }

    public function test_destroy(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create();

        $this->actingAs($user)->delete(
            route('projects.destroy', $project)
        )->assertSessionHasNoErrors()
            ->assertStatus(302);
    }

}
