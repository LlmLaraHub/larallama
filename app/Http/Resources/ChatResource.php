<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'title' => $this->title ?? 'Chat #'.$this->id,
            'chatable_id' => $this->chatable_id,
            'chatable_type' => $this->chatable_type,
            'chat_status' => $this->chat_status?->value,
            'chat_status_formatted' => str($this->chat_status?->name)->headline(),
            'user_id' => new UserResource($this->user),
        ];
    }
}
