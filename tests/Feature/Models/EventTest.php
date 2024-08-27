<?php

namespace Tests\Feature\Models;

use App\Models\Event;
use Tests\TestCase;

class EventTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_events(): void
    {
        $model = Event::factory()->create();
        $this->assertNotNull($model->collection?->id);
        $this->assertNotNull($model->assigned_to?->id);

        $this->assertNotNull($model->start_date);
        $this->assertNotNull($model->end_date);
    }
}
