<?php

namespace App\Models;

use App\Domains\Documents\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LlmLaraHub\LlmDriver\HasDrivers;
use Pgvector\Laravel\Vector;

/**
 * @property Document $document
 */
class DocumentChunk extends Model implements HasDrivers
{
    use HasFactory;

    protected $casts = [
        'embedding_3072' => Vector::class,
        'embedding_1536' => Vector::class,
        'embedding_2048' => Vector::class,
        'embedding_4096' => Vector::class,
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

    public function getEmbeddingDriver(): string
    {
        return $this->document->collection->embedding_driver->value;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return DocumentChunk::class;
    }

    public function getDriver(): string
    {
        return $this->document->collection->driver->value;
    }

    public function getEmbeddingColumn(): string
    {

        return get_embedding_size($this->getEmbeddingDriver());

    }

    public function getSummary(): string
    {
        return $this->summary;
    }

}
