<?php

namespace App\Models;

use App\Domains\Projects\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LlmLaraHub\LlmDriver\HasDrivers;

class Project extends Model implements HasDrivers
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => StatusEnum::class,
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function chats(): MorphMany
    {
        return $this->morphMany(Chat::class, 'chatable');
    }

    public function getDriver(): string
    {
        return $this->chats()->first()->chat_driver?->value;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('end_date', '>=', now())
            ->orWhere('end_date', null);
    }

    public function getEmbeddingDriver(): string
    {
        return $this->chats()->first()->embedding_driver->value;
    }

    public function getSummary(): string
    {
        return $this->content;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->chats()->first()->chatable_type;
    }

    public function getChatable(): HasDrivers
    {
        return $this;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getChat(): ?Chat
    {
        return $this->chats->first();
    }

    public function getContent(): string
    {
        $context = $this->content;
        $now = now()->toISOString();

        return <<<PROMPT
        Current Date: $now

        $context

        PROMPT;
    }

    public function getSystemPrompt(): string
    {
        $context = $this->system_prompt;
        $now = now()->toISOString();

        return <<<PROMPT
        Current Date: $now

        $context

        PROMPT;
    }
}
