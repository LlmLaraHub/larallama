<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
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
            'title' => str($this->title)->remove(['#'])->trim()->limit(50)->toString(),
            'content' => $this->content,
            'content_formatted' => str($this->content)->markdown(),
            'type' => $this->type->name,
            'votes' => $this->votes,
            'document' => $this->document,
            'section' => $this->section,
        ];
    }
}
