<?php

use App\Models\Project;
use App\Models\Transformer;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\assertDatabaseCount;

it('should show the form for URL Transformer [RESOURCE_NAME]', function () {
    $user = User::factory()->withPersonalTeam()
        ->create();

    $user = $this->createTeam($user);

    $project = Project::factory()->create([
        'team_id' => $user->current_team_id,
    ]);

    $this->actingAs($user)
        ->get(route('transformers.[RESOURCE_KEY].create', [
            'project' => $project->id,
        ]))
        ->assertOk();
});

it('should allow you to edit [RESOURCE_NAME]', function () {
    $user = User::factory()->withPersonalTeam()
        ->create();

    $user = $this->createTeam($user);

    $project = Project::factory()->create([
        'team_id' => $user->current_team_id,
    ]);

    $transformer = Transformer::factory()->create([
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->get(route('transformers.[RESOURCE_KEY].edit', [
            'project' => $project->id,
            'transformer' => $transformer->id,
        ]))
        ->assertOk();
});

it('should run [RESOURCE_NAME]', function () {
    Queue::fake();
    $user = User::factory()->withPersonalTeam()
        ->create();

    $user = $this->createTeam($user);

    $project = Project::factory()->create([
        'team_id' => $user->current_team_id,
    ]);

    $transformer = Transformer::factory()->create([
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->post(route('transformers.[RESOURCE_KEY].run', [
            'project' => $project->id,
            'source' => $transformer->id,
        ]))
        ->assertRedirectToRoute('projects.show', [
            'project' => $project->id,
        ]);
});

it('should allow you to update [RESOURCE_NAME]', function () {
    $user = User::factory()->withPersonalTeam()
        ->create();

    $user = $this->createTeam($user);

    $project = Project::factory()->create([
        'team_id' => $user->current_team_id,
    ]);

    $transformer = Transformer::factory()->create([
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->put(route('sources.[RESOURCE_KEY].update', [
            'project' => $project->id,
            'transformer' => $transformer->id,
        ]), [
            'name' => 'Foo',
            'meta_data' => [
                'url' => 'https://foo.bar',
            ],
            'description' => 'Bar',
        ])
        ->assertRedirectToRoute('projects.show', [
            'project' => $project->id,
        ]);

    expect($transformer->refresh()->name)->toBe('Foo');
});

it('should create [RESOURCE_NAME]', function () {
    $user = User::factory()->withPersonalTeam()
        ->create();

    $user = $this->createTeam($user);

    $project = Project::factory()->create([
        'team_id' => $user->current_team_id,
    ]);

    assertDatabaseCount('transformers', 0);

    $this->actingAs($user)
        ->post(route('transformers.[RESOURCE_KEY].store', [
            'project' => $project->id,
        ]), [
            'name' => 'Foo',
            'description' => 'Bar',
            'meta_data' => [
                'url' => 'https://foo.bar',
            ],
        ])
        ->assertRedirectToRoute('projects.show', [
            'project' => $project->id,
        ]);
    assertDatabaseCount('transformers', 1);
});
