<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'from_ai' => $this->from_ai,
            'initials' => ($this->from_ai) ? 'Ai' : 'You',
            'type' => 'text', //@TODO
            'body' => $this->body,
            'body_markdown' => str($this->body)->markdown(),
            'diff_for_humans' => $this->created_at->diffForHumans(),
        ];
    }
}
