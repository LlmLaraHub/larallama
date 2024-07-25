<?php

namespace Tests\Feature\Models;

use App\Models\Message;
use App\Models\Report;
use App\Models\Section;
use Tests\TestCase;

class ReportTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $model = Report::factory()->create([
            'message_id' => Message::factory(),
        ]);

        $section = Section::factory()->create([
            'report_id' => $model->id,
        ]);

        $this->assertNotNull($model->user_id);
        $this->assertNotNull($model->chat_id);
        $this->assertNotNull($model->type);
        $this->assertNotNull($model->message->id);
        $this->assertNotNull($model->user->id);
        $this->assertNotNull($model->user_message->id);
        $this->assertNotNull($model->reference_collection->id);
        $this->assertNotNull($model->sections->first()->id);
        $this->assertNotNull($section->report->id);
        $this->assertNotNUll($model->status_sections_generation);
        $this->assertNotNUll($model->status_entries_generation);
    }
}
