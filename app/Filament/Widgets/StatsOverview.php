<?php

namespace App\Filament\Widgets;

use App\Models\Chat;
use App\Models\Document;
use App\Models\Event;
use App\Models\Message;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {

        return [
            $this->getDocuments(),
            $this->getChatMessages(), $this->getDocuments(), $this->getEvents(), $this->getMessages()];
    }

    protected function getChatMessages()
    {
        $title = 'Chats';
        $description = 'Chats in the past 7 days';

        return $this->getTrend($title, $description, Chat::class);
    }

    protected function getDocuments()
    {
        $title = 'Documents';
        $description = 'Documents in the past 7 days';

        return $this->getTrend($title, $description, Document::class);
    }

    protected function getEvents()
    {
        $title = 'Events';
        $description = 'Events in the past 7 days';

        return $this->getTrend($title, $description, Event::class);
    }

    protected function getMessages()
    {
        $title = 'Messages';
        $description = 'Messages in the past 7 days';

        return $this->getTrend($title, $description, Message::class);
    }

    protected function getTrend(
        string $title,
        string $description,
        string $model,
    ) {

        $data = Trend::model($model)
            ->between(
                start: now()->subDays(7),
                end: now()->endOfDay(),
            )
            ->perDay()
            ->count();

        return Stat::make($title, $data->map(fn (TrendValue $value) => $value->aggregate)->sum())
            ->description($description)
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart($data->map(fn (TrendValue $value) => $value->aggregate)->toArray())
            ->color('success');
    }
}
