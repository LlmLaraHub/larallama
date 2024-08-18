<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Widgets\CalendarWidget;
use Filament\Resources\Pages\Page;

class CalendarPage extends Page
{
    protected static string $resource = EventResource::class;

    protected static string $view = 'filament.resources.event-resource.pages.calendar-page';

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }
}
