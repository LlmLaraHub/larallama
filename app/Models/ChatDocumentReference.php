<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatDocumentReference extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function document_chunk(): BelongsTo
    {
        return $this->belongsTo(DocumentChunk::class);
    }
}
