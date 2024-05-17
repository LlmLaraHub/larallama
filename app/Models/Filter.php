<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filter extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function collection() : BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function documents() : BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }

}
