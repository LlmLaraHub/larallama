<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_path' => $this->file_path,
            'summary' => $this->summary,
            'type' => str($this->type->name)->title()->toString(),
            'status' => str($this->status->name)->headline()->toString(),
            'document_chunks_count' => $this->document_chunks()->count(),
            'tags' => TagResource::collection($this->tags),
        ];
    }
}
