<?php

namespace App\Models;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Document
 *
 * @property int $id
 * @property int $collection_id
 * @property string|null $summary
 * @property string|null $file_path

 */
class Document extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => TypesEnum::class,
        'status' => StatusEnum::class,
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function document_chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }

    public function pathToFile(): ?string
    {
        return sprintf(
            storage_path('app/collections/%d/%s'),
            $this->collection_id,
            $this->file_path
        );
    }

    public function mkdirPathToFile(): ?string
    {
        return sprintf(
            storage_path('app/collections/%d'),
            $this->collection_id
        );
    }
}
