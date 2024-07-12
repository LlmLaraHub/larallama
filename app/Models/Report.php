<?php

namespace App\Models;

use App\Domains\Reporting\ReportTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LlmLaraHub\LlmDriver\HasDrivers;

class Report extends Model implements HasDrivers
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

    public function getDriver(): string
    {
        return $this->chat->getDriver();
    }

    public function getEmbeddingDriver(): string
    {
        return $this->chat->getEmbeddingDriver();
    }

    public function getSummary(): string
    {
        return $this->chat->getSummary();
    }

    public function getId(): int
    {
        return $this->chat->getChat()->id;
    }

    public function getType(): string
    {
        return $this->chat->getType();
    }

    public function getChatable(): HasDrivers
    {
        return $this->chat->getChatable();
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }
}
