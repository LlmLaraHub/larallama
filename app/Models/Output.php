<?php

namespace App\Models;

use App\Domains\Outputs\OutputTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property Collection $collection
 * @property bool $public
 * @property bool $active
 */
class Output extends Model
{
    use HasFactory;
    use HasSlug;

    protected $guarded = [];

    protected $casts = [
        'type' => OutputTypeEnum::class,
        'meta_data' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
