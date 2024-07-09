<?php

namespace App\Models;

use App\Domains\Reporting\ReportTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'type' => ReportTypeEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }


    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function reference_collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'reference_collection_id');
    }
}
