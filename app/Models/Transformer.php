<?php

namespace App\Models;

use App\Domains\Transformers\TypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $transformable_type
 * @property int $transformable_id
 * @property TypeEnum $type
 */
class Transformer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'last_run' => 'datetime',
        'type' => TypeEnum::class,
        'active' => 'boolean',
    ];

    public function transformable(): MorphTo
    {
        return $this->morphTo();
    }
}
