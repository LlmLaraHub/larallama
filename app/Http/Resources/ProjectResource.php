<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'status' => $this->status->value,
            'status_formatted' => str($this->status->name)->headline(),
            'content' => $this->content,
            'system_prompt' => $this->system_prompt,
            'users' => $this->team->allUsers(),
            'team' => TeamResource::make($this->team),
            'content_formatted' => str($this->content)->markdown(),
            'system_prompt_formatted' => str($this->system_prompt)->markdown(),
        ];
    }
}
