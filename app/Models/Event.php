<?php

namespace App\Models;

use App\Domains\Events\EventTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => EventTypes::class,
        'start_date' => 'date',
        'start_time' => 'timestamp',
        'all_day' => 'boolean',
        'assigned_to_assistant' => 'boolean',
        'end_date' => 'date',
        'end_time' => 'timestamp',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function assigned_to(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
}
