<?php

namespace App\Models;

use App\Domains\Documents\StatusEnum;
use App\Domains\UnStructured\StructuredTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\TagFunction\Contracts\TaggableContract;
use LlmLaraHub\TagFunction\Helpers\Taggable;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

/**
 * @property Document $document
 * @property StructuredTypeEnum $type
 */
class DocumentChunk extends Model implements HasDrivers, TaggableContract
{
    use HasFactory;
    use HasNeighbors;
    use Taggable;

    protected $casts = [
        'embedding_3072' => Vector::class,
        'embedding_1536' => Vector::class,
        'embedding_2048' => Vector::class,
        'embedding_4096' => Vector::class,
        'status_embeddings' => StatusEnum::class,
        'status_tagging' => StatusEnum::class,
        'status_summary' => StatusEnum::class,
        'meta_data' => 'array',
        'type' => StructuredTypeEnum::class,
    ];

    protected $guarded = [];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function siblingTags(): array
    {
        return [];
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

    public function getChatable(): HasDrivers
    {
        return $this->document->collection;
    }

    public function getChat(): ?Chat
    {
        /**
         * @TODO
         * I need to come back to this
         */
        return $this->document->collection->chats()->first();
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
        return $this->content;
    }
}
