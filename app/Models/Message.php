<?php

namespace App\Models;

use App\Domains\Messages\RoleEnum;
use App\Events\MessageCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    public $fillable = [
        'body',
        'role',
        'in_out',
        'is_chat_ignored',
    ];

    protected $dispatchesEvents = [
        'created' => MessageCreatedEvent::class,
    ];

    protected $casts = [
        'role' => RoleEnum::class,
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
}
