<?php

namespace App\Models;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LlmLaraHub\LlmDriver\HasDrivers;

/**
 * @property string $slug
 */
class Source extends Model implements HasDrivers
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'meta_data' => 'array',
        'secrets' => 'encrypted:array',
        'active' => 'bool',
        'last_run' => 'datetime',
        'type' => SourceTypeEnum::class,
        'recurring' => RecurringTypeEnum::class,
    ];

    protected static function booted(): void
    {
        static::created(function (Source $source) {
            if (! $source->slug) {
                $source->slug = Str::random(12);
                $source->updateQuietly();
            }
        });
    }

    public function getChatable(): HasDrivers
    {
        return $this->collection->getChatable();
    }

    public function getChat(): ?Chat
    {
        /**
         * @TODO
         * I need to come back to this
         */
        return $this->collection->chats()->first();
    }

    public function getSummary(): string
    {
        return $this->collection->getSummary();
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function getId(): int
    {
        return $this->collection->getId();
    }

    public function getDriver(): string
    {
        return $this->collection->getDriver();
    }

    public function getEmbeddingDriver(): string
    {
        return $this->collection->getEmbeddingDriver();
    }

    public function getType(): string
    {
        return $this->collection->getType();
    }

    public function run(): void
    {
        $class = 'App\\Domains\\Sources\\'.$this->type->name;

        if (! class_exists($class)) {
            throw new \Exception('Source Class does not exist '.$class);
        }

        $class = app()->make($class);

        $class->handle($this);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function scopeSlug($query, string $slug)
    {
        return $query->whereSlug($slug);
    }

    public function transformers(): MorphMany
    {
        return $this->morphMany(Transformer::class, 'transformable');
    }
}
