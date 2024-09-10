<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_index(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create();

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        $this->actingAs($user)->get(
            route('tasks.index', [
                'project' => $project->id,
            ])
        )->assertStatus(200);
    }
}
