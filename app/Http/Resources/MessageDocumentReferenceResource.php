<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageDocumentReferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tags = $this->document_chunk?->tags;
        if ($tags) {
            $tags = TagResource::collection($tags);
        }

        return [
            'id' => $this->id,
            'document_name' => $this->document_chunk?->document->file_path,
            'page' => $this->document_chunk?->sort_order,
            'distance' => round($this->distance, 2),
            'document_chunk_id' => $this->document_chunk_id,
            'section_number' => $this->document_chunk?->section_number + 1, //since 0 does not look good in the ui
            'summary' => str($this->document_chunk?->content)->markdown(),
            'taggings' => $tags,
        ];
    }
}
