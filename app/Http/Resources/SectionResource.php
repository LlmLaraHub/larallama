<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
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
            'subject' => $this->subject,
            'content' => $this->content,
            'content_formatted' => str($this->content)->markdown(),
            'sort_order' => $this->sort_order,
            'document' => new ChatDocumentResource($this->document),
            'prompt' => str($this->prompt)->markdown(),
            'entries' => EntryResource::collection($this->entries),
        ];
    }
}
