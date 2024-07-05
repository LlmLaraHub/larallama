<?php

namespace App\Models;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Chat\ToolsDto;
use App\Domains\Messages\RoleEnum;
use App\Events\MessageCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LlmLaraHub\LlmDriver\HasDrivers;

class Message extends Model implements HasDrivers
{
    use HasFactory;

    public $fillable = [
        'body',
        'role',
        'in_out',
        'is_chat_ignored',
        'meta_data',
        'tools',
    ];

    protected $dispatchesEvents = [
        'created' => MessageCreatedEvent::class,
    ];

    protected $casts = [
        'role' => RoleEnum::class,
        'tools' => ToolsDto::class,
        'meta_data' => MetaDataDto::class,
        'in_out' => 'boolean',
    ];

    /**
     * Return true if the message is from the user.
     */
    public function getFromUserAttribute(): bool
    {
        return $this->role === RoleEnum::User;
    }

    /**
     * Return true if the message is from the AI.
     */
    public function getFromAiAttribute(): bool
    {
        return $this->role !== RoleEnum::User;
    }

    /**
     * Return a compressed message
     */
    public function getCompressedBodyAttribute(): string
    {
        return $this->compressMessage($this->body);
    }

    /**
     * Compress a message
     */
    public function compressMessage($message): array|string|null
    {
        if (! config('temp.compressed_messages')) {
            return $message;
        }

        // Remove spaces
        $body = str_replace(' ', '', $message);

        // Remove punctuation
        return preg_replace('/\p{P}/', '', $body);

    }

    /**
     * Return the chat that the message belongs to.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function message_document_references(): HasMany
    {
        return $this->hasMany(MessageDocumentReference::class);
    }

    public function prompt_histories(): HasMany
    {
        return $this->hasMany(PromptHistory::class);
    }

    public function getLatestMessages(): array
    {
        return $this->chat->getChatResponse();
    }

    public function getFilter(): ?Filter
    {
        $filter = data_get($this->meta_data, 'filter');

        if ($filter) {
            $filter = Filter::findOrFail($filter);
        }

        return $filter;
    }

    public function getContent(): string
    {
        return $this->body;
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
        return $this->chat->getId();
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
