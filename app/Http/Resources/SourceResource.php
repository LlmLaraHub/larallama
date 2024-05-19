<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $recurring = $this->recurring;
        if($recurring) {
            $recurring = str($recurring->name)->headline()->toString();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'collection_id' => $this->collection_id,
            'details' => $this->details,
            'active' => $this->active ? "Yes" : "No",
            'recurring' => $recurring,
            'description' => $this->description,
            'type' => str($this->type->name)->headline()->toString(),
        ];
    }
}
