<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicOutputResource extends JsonResource
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
            'title' => $this->title,
            'collection_id' => $this->collection_id,
            'summary' => str($this->summary)->markdown(),
            'summary_truncated' => str($this->summary)->limit(128)->markdown(),
            'active' => $this->active,
            'public' => $this->public,
            'slug' => $this->slug,
            'url' => route('collections.outputs.web_page.show', [
                'output' => $this->slug,
            ]),
        ];
    }
}
