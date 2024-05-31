<?php

use App\Models\Outbound;
use App\Models\Project;
use App\Models\User;
use App\Outbound\OutboundEnum;
use function Pest\Laravel\assertDatabaseCount;

it('test shows create page [RESOURCE_NAME] Outbound', function () {
    $user = User::factory()->withPersonalTeam()
        ->create();

    $user = $this->createTeam($user);

    $project = Project::factory()->create([
        'team_id' => $user->current_team_id,
    ]);

    assertDatabaseCount('outbounds', 0);

    $this->actingAs($user)
        ->get(route('outbounds.[RESOURCE_KEY].create', [
            'project' => $project->id,
        ]))
        ->assertRedirectToRoute('outbounds.[RESOURCE_KEY].show', [
            'project' => $project->id,
            'outbound' => Outbound::first()->id,
        ]);
    assertDatabaseCount('outbounds', 1);
});

it('test show [RESOURCE_NAME] Outbound', function () {
    $user = User::factory()->withPersonalTeam()
        ->create();

    $user = $this->createTeam($user);

    $project = Project::factory()->create([
        'team_id' => $user->current_team_id,
    ]);

    $outbound = Outbound::factory()->create([
        'type' => OutboundEnum::[RESOURCE_CLASS_NAME],
        'project_id' => $project->id
    ]);

    $this->actingAs($user)
        ->get(route('outbounds.[RESOURCE_KEY].show', [
            'project' => $project->id,
            'outbound' => $outbound->id,
        ]))
        ->assertOk();
});
