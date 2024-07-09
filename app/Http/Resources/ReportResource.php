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
            'reference_collection_id' => new CollectionResource($this->reference_collection),
            'sections' => SectionResource::collection($this->sections),
        ];
    }
}
