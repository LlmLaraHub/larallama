<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = 2;

    public function fetchEvents(array $info): array
    {

        return Event::query()
            ->where('start_date', '>=', $info['start'])
            ->where('end_date', '<=', $info['end'])
            ->get()
            ->map(
                fn (Event $event) => [
                    'title' => $event->title,
                    'start' => $event->start,
                    'end' => $event->end,
                    'url' => EventResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    'shouldOpenUrlInNewTab' => true,
                ]
            )
            ->all();

    }
}
