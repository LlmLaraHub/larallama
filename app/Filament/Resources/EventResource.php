<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Collection;
use App\Models\Event;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Event::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('collection.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->sortable()
                    ->dateTime('Y-m-d h:i'),
                Tables\Columns\TextColumn::make('end_date')
                    ->sortable()
                    ->dateTime('Y-m-d h:i'),
                Tables\Columns\TextColumn::make('location')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->options(Event::distinct('location')->orderBy('location')->pluck('location', 'location')),
                Tables\Filters\SelectFilter::make('title')
                    ->options(Event::distinct('title')->orderBy('title')->pluck('title', 'title')),
                DateRangeFilter::make('start_date'),
                Tables\Filters\SelectFilter::make('collection_id')
                    ->label('Collection')
                    ->options(Collection::orderBy('name')->get()->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'calendar' => Pages\CalendarPage::route('/calendar'),
        ];
    }
}
