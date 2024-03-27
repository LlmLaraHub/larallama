<?php

namespace App\Models;

use App\Domains\Documents\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\Vector;

/**
 * @property Document $document
 */
class DocumentChunk extends Model
{
    use HasFactory;

    protected $casts = [
        'embedding' => Vector::class,
        'status_embeddings' => StatusEnum::class,
        'status_tagging' => StatusEnum::class,
        'status_summary' => StatusEnum::class,
    ];

    protected $guarded = [];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function ($document_chunk) {
            $document_chunk->original_content = $document_chunk->getOriginal('content');
            $document_chunk->saveQuietly();
        });
    }

    public function getDriver(): string
    {
        return $this->document->collection->driver;
    }
}
