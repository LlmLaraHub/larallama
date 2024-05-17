<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Filter extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
