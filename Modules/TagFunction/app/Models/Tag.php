<?php

namespace LlmLaraHub\TagFunction\Models;

use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LlmLaraHub\TagFunction\Database\Factories\TagFactory;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    public function document_chunks(): MorphToMany
    {
        return $this->morphedByMany(DocumentChunk::class, 'taggable');
    }

    public function documents(): MorphToMany
    {
        return $this->morphedByMany(Document::class, 'taggable');
    }

    public function collections(): MorphToMany
    {
        return $this->morphedByMany(Collection::class, 'taggable');
    }
}
