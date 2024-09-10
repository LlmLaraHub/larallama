<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResourceShow extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $chat = $this->chats()->latest()->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'status' => $this->status->value,
            'chat' => ChatResource::make($chat),
            'status_formatted' => str($this->status->name)->headline(),
            'content' => $this->content,
            'system_prompt' => $this->system_prompt,
            'team' => TeamResource::make($this->team),
        ];
    }
}
