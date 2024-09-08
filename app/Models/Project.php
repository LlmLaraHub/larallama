<?php

namespace App\Models;

use App\Domains\Projects\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => StatusEnum::class,
    ];

    public function team() : BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
