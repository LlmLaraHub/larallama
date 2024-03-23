<?php

namespace Tests\Feature\Models;

use Tests\TestCase;

class ProjectTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = \App\Models\Project::factory()->create();

        $this->assertNotNull($model->team->id);

    }
}
