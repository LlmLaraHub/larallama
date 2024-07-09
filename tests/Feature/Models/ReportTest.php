<?php

namespace Tests\Feature\Models;

use App\Models\Report;
use Tests\TestCase;

class ReportTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $model = Report::factory()->create();

        $this->assertNotNull($model->user_id);
        $this->assertNotNull($model->chat_id);
        $this->assertNotNull($model->type);
        $this->assertNotNull($model->user->id);
        $this->assertNotNull($model->reference_collection->id);
    }
}
