<?php

namespace App\Models;

use App\Domains\Reporting\EntryTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => EntryTypeEnum::class,
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
