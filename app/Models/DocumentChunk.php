<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\Vector;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $casts = [
        'embedding' => Vector::class,
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
}
