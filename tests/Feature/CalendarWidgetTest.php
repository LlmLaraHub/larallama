<?php

namespace Tests\Feature;

use App\Filament\Widgets\CalendarWidget;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CalendarWidgetTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_moves_event(): void
    {
        $event = Event::factory()->create([
            'start_date' => '2024-08-07',
            'end_date' => '2024-08-07',
        ]);

        $updated = $event->toArray();

        $updated['start'] = '2024-08-08 04:00:00';
        $updated['end'] = '2024-08-08 04:00:00';

        $widget = new CalendarWidget();
        $widget->onEventDrop(
                event: $updated,
                oldEvent: $event->toArray(),
                relatedEvents: [],
                delta: [],
                oldResource: null,
                newResource: null
            );

        $this->assertEquals(Carbon::parse('2024-08-08 04:00:00'),
            $widget->record->start_time);
        $this->assertEquals(Carbon::parse('2024-08-08 04:00:00'),
            $widget->record->start_date);
    }

    public function test_no_end_date()
    {

    }
}
