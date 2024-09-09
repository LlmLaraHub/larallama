<?php

namespace Tests\Feature\Models;

use App\Models\Task;
use Tests\TestCase;

class TaskTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $task = Task::factory()->create();
        $this->assertNotNull($task->id);
        $this->assertNotNull($task->name);
        $this->assertNotNull($task->details);
        $this->assertNotNull($task->due_date);
        $this->assertNotNull($task->assistant);
        $this->assertNotNull($task->project->id);
        $this->assertNotNull($task->user->id);
    }

    public function test_not_completed(): void
    {
        Task::factory()->create(
            [
                'completed_at' => null,
            ]
        );

        Task::factory()->create([
            'completed_at' => now(),
        ]);

        $this->assertCount(1, Task::notCompleted()->get());
    }
}
