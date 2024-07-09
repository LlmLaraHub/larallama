<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SectionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $model = \App\Models\Section::factory()->create();
        $this->assertNotNull($model->document_id);
        $this->assertNotNull($model->report_id);
        $this->assertNotNUll($model->document->id);
        $this->assertNotNUll($model->report->id);
    }
}
