<?php

namespace App\Models;

use App\Domains\Documents\StatusEnum;
use App\LlmDriver\HasDrivers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function getDriver(): string
    {
        return $this->document->collection->driver->value;
    }

    public function getEmbeddingColumn() : string {

        $embeddingModel = driverHelper($this->getDriver(), 'embedding_model');

        $size = config('llmdriver.embedding_sizes.' . $embeddingModel);

        if($size) {
            return "embedding_" . $size;
        }

        return "embeding_3072";
    }
}
