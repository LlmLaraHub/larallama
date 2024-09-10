<?php

namespace Tests\Feature\Models;

use App\Models\Project;
use App\Models\Task;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $model = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $model->id,
        ]);
        $this->assertNotNull($model->name);
        $this->assertNotNull($model->status);
        $this->assertNotNull($model->content);
        $this->assertNotNull($model->team->id);
        $this->assertNotNull($model->tasks->first()->id);
    }

    public function test_active() {
        $model = Project::factory()->create([
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(2),
        ]);

        $this->assertEmpty(Project::active()->get());
    }

}
