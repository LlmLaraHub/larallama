<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

/**
 * @see https://github.com/saade/filament-fullcalendar/blob/3.x/src/Widgets/Concerns/InteractsWithEvents.php
 */
class CalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = 2;

    public Model|string|null $model = Event::class;

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function viewAction(): Action
    {
        return Actions\ViewAction::make();
    }

    public function getFormSchema(): array
    {
        return Event::getForm();
    }

    public function fetchEvents(array $info): array
    {

        return Event::query()
            ->where('start_date', '>=', $info['start'])
            ->where('end_date', '<=', $info['end'])
            ->get()
            ->map(
                fn (Event $event) => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_date,
                    'end' => $event->end_date,
                    'url' => EventResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    'shouldOpenUrlInNewTab' => true,
                ]
            )
            ->all();

    }

    /**
     * Triggered when dragging stops and the event has moved to a different day/time.
     *
     * @param  array  $event  An Event Object that holds information about the event (date, title, etc) after the drop.
     * @param  array  $oldEvent  An Event Object that holds information about the event before the drop.
     * @param  array  $relatedEvents  An array of other related Event Objects that were also dropped. An event might have other recurring event instances or might be linked to other events with the same groupId
     * @param  array  $delta  A Duration Object that represents the amount of time the event was moved by.
     * @param  ?array  $oldResource  A Resource Object that represents the previously assigned resource.
     * @param  ?array  $newResource  A Resource Object that represents the newly assigned resource.
     * @return bool Whether to revert the drop action.
     */
    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        if ($this->getModel()) {
            $this->record = $this->resolveRecord($event['id']);
        }

        Log::info('Event dropped', ['event' => $event]);

        $start_date = data_get($event, 'start');
        $end_date = data_get($event, 'end', $start_date);

        /** @phpstan-ignore-next-line */
        $this->record->start_date = $start_date;
        /** @phpstan-ignore-next-line */
        $this->record->end_date = $end_date;

        $this->mountAction('edit', [
            'type' => 'drop',
            'event' => $event,
            'oldEvent' => $oldEvent,
            'relatedEvents' => $relatedEvents,
            'delta' => $delta,
            'oldResource' => $oldResource,
            'newResource' => $newResource,
        ]);

        return false;
    }
}
