<?php

namespace App\Models;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Recurring\RecurringTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property Collection $collection
 * @property bool $public
 * @property bool $active
 * @property Carbon $last_run
 * @property RecurringTypeEnum $recurring
 */
class Output extends Model
{
    use HasFactory;
    use HasSlug;

    protected $guarded = [];

    protected $casts = [
        'last_run' => 'datetime',
        'type' => OutputTypeEnum::class,
        'recurring' => RecurringTypeEnum::class,
        'meta_data' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function scopeActive($query) {
        return $query->where('active', 1);
    }

    public function run(): void
    {
        $class = 'App\\Domains\\Outputs\\'.$this->type->name;

        if (! class_exists($class)) {
            throw new \Exception('Output Class does not exist '.$class);
        }

        $class = app()->make($class);

        $class->handle($this);
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

    public function fromMetaData(string $key): mixed
    {
        $meta_data = $this->meta_data;

        return data_get($meta_data, $key, false);
    }
}
