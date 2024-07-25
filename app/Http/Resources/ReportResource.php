<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'user_id' => $this->user_id,
            'chat_id' => $this->chat_id,
            'type' => $this->type,
            'type_formatted' => str($this->type->name)->headline()->toString(),
            'reference_collection' => new CollectionResource($this->reference_collection),
            'sections' => SectionResource::collection($this->sections),
            'status_sections_generation' => $this->status_sections_generation?->value,
            'status_entries_generation' => $this->status_entries_generation?->value,
            'status_sections_generation_formatted' => $this->status_sections_generation?->name,
            'status_entries_generation_formatted' => $this->status_entries_generation?->name,
        ];
    }
}
