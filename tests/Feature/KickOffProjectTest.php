<?php

namespace Tests\Feature;

use App\Domains\Projects\KickOffProject;
use App\Models\Chat;
use App\Models\Project;
use App\Models\User;
use Facades\App\Domains\Projects\Orchestrate;
use Tests\TestCase;

class KickOffProjectTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_kickoff(): void
    {
        $project = Project::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_id' => $project->id,
            'chatable_type' => Project::class,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        Orchestrate::shouldReceive('handle')->once();

        (new KickOffProject())->handle($project);

    }
}
