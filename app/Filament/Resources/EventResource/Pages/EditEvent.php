<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mountUsing(
                    function (Event $record, Form $form, array $arguments) {
                        put_fixture('event_arguments.json', $arguments);
                        $form->fill([
                            'title' => $record->title,
                            'start_date' => $arguments['event']['start'] ?? $record->start_date,
                            'end_date' => $arguments['event']['end'] ?? $record->end_date,
                        ]);
                    }
                ),
            Actions\DeleteAction::make(),
        ];
    }
}
