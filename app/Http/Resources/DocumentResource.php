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
        $summary = $this->summary;
        if(!empty($summary)) {
            $summary = str($this->summary)->markdown();
        }
        return [
            'id' => $this->id,
            'file_path' => $this->file_path,
            'subject' => $this->subject,
            'link' => $this->link,
            'summary' => $this->summary,
            'summary_markdown' => $summary,
            'type' => str($this->type->name)->title()->toString(),
            'status' => str($this->status->name)->headline()->toString(),
            'document_chunks_count' => $this->document_chunks()->where('section_number', 0)->count(),
            'tags' => TagResource::collection($this->tags),
            'tags_count' => $this->tags->count(),
            'children_count' => $this->children->count(),
            'parent_id' => $this->parent_id,
            'children' => DocumentResource::collection($this->children),
        ];
    }
}
