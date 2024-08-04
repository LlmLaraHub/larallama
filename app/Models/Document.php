<?php

namespace App\Models;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\UnStructured\StructuredTypeEnum;
use App\Helpers\TextChunker;
use App\Jobs\DocumentProcessingCompleteJob;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\HasDriversTrait;
use LlmLaraHub\TagFunction\Contracts\TaggableContract;
use LlmLaraHub\TagFunction\Helpers\Taggable;
use LlmLaraHub\TagFunction\Jobs\TagDocumentJob;
use LlmLaraHub\TagFunction\Models\Tag;

/**
 * Class Document
 *
 * @property int $id
 * @property int $collection_id
 * @property string|null $summary
 * @property string|null $original_content
 * @property string|null $file_path
 * @property StructuredTypeEnum $child_type
 */
class Document extends Model implements HasDrivers, TaggableContract
{
    use HasFactory;
    use Taggable;
    use HasDriversTrait;

    protected $guarded = [];

    protected $casts = [
        'type' => TypesEnum::class,
        'child_type' => StructuredTypeEnum::class,
        'status' => StatusEnum::class,
        'meta_data' => 'array',
        'summary_status' => StatusEnum::class,
    ];

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class);
    }

    public function siblingTags(): array
    {
        return Tag::query()
            ->select('tags.name')
            ->join('taggables', 'taggables.tag_id', '=', 'tags.id')
            ->join('document_chunks', 'document_chunks.document_id', '=', 'taggables.taggable_id')
            ->where('taggables.taggable_type', '=', DocumentChunk::class)
            ->where('document_chunks.document_id', '=', $this->id)
            ->distinct('name')
            ->get()
            ->pluck('name')
            ->toArray();
    }

    public function getContentAttribute(): string
    {
        return $this->summary;
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function getChatable(): HasDrivers
    {
        return $this->collection;
    }

    public function document_chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }

    public function transformer(): BelongsTo
    {
        return $this->belongsTo(Transformer::class);
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return Document::class;
    }

    public function getChat(): ?Chat
    {
        /**
         * @TODO
         * I need to come back to this
         */
        return $this->collection->chats()->first();
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

    public function getDriver(): string
    {
        return $this->collection->driver->value;
    }

    public function getEmbeddingDriver(): string
    {
        return $this->collection->embedding_driver->value;
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Document::class, 'parent_id');
    }

    public static function make(
        string $content,
        Collection|HasDrivers $collection,
        ?string $filePath = null
    ): Document {
        return Document::create([
            'file_path' => $filePath,
            'collection_id' => $collection->id,
            'type' => TypesEnum::Txt,
            'subject' => str($content)->limit(256)->toString(),
            'original_content' => $content,
            'status_summary' => StatusEnum::Pending,
        ]);
    }

    public function vectorizeDocument(): void
    {
        $document = $this;
        $jobs = [];
        $page_number = 1;
        $chunked_chunks = TextChunker::handle($document->original_content);
        foreach ($chunked_chunks as $chunkSection => $chunkContent) {
            try {
                $guid = md5($chunkContent);
                $DocumentChunk = DocumentChunk::updateOrCreate(
                    [
                        'document_id' => $document->id,
                        'sort_order' => $page_number,
                        'section_number' => $chunkSection,
                    ],
                    [
                        'guid' => $guid,
                        'content' => $chunkContent,
                        'sort_order' => $page_number,
                    ]
                );

                $jobs[] = [
                    new VectorlizeDataJob($DocumentChunk),
                ];
                notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Document Created working on pages');
            } catch (\Exception $e) {
                Log::error('Error parsing PDF', ['error' => $e->getMessage()]);
            }
        }

        Bus::batch($jobs)
            ->name("Chunking Document - $document->file_path")
            ->finally(function (Batch $batch) use ($document) {
                Bus::batch([
                    [
                        new SummarizeDocumentJob($document),
                        new TagDocumentJob($document),
                        new DocumentProcessingCompleteJob($document),
                    ],
                ])
                    ->name('Finalizing Documents')
                    ->allowFailures()
                    ->dispatch();
            })
            ->allowFailures()
            ->dispatch();
    }
}
