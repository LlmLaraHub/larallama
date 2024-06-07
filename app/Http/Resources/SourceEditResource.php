<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SourceEditResource extends JsonResource
{
    use HelperTrait;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lastRun = $this->getLastRun();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'collection_id' => $this->collection_id,
            'details' => $this->details,
            'active' => $this->active,
            'recurring' => $this->recurring->value,
            'description' => $this->description,
            'slug' => $this->slug,
            'last_run' => $lastRun,
            'type_key' => $this->type->value,
            'meta_data' => $this->meta_data,
            'meta_data_encoded' => json_encode($this->meta_data, 128),
            'secrets' => $this->secrets,
            'type' => str($this->type->name)->headline()->toString(),
        ];
    }
}
