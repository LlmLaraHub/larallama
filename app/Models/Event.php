<?php

namespace App\Models;

use App\Domains\Events\EventTypes;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Carbon $start_date
 * @property Carbon $end_date
 */
class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => EventTypes::class,
        'start_date' => 'datetime',
        'all_day' => 'boolean',
        'assigned_to_assistant' => 'boolean',
        'end_date' => 'datetime',
    ];

    public static function getForm(): array
    {
        return [
            Section::make('Event')
                ->description('Manage an Event')

                ->columns(1)
                ->schema([
                    TextInput::make('title')->required(),
                    Select::make('collection_id')
                        ->searchable()
                        ->preload()
                        ->relationship(name: 'collection', titleAttribute: 'name')
                        ->label('Collection'),
                    TextInput::make('description')->required(),
                    TextInput::make('location'),
                ]),
            Section::make('Dates')
                ->description('Manage an Event')

                ->columns(2)
                ->schema([
                    DateTimePicker::make('start_date')
                        ->native(false)
                        ->required(),
                    DateTimePicker::make('end_date')
                        ->native(false)
                        ->required(),
                ]),
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function assigned_to(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
}
