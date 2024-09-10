<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'details' => $this->details,
            'completed_at' => $this->completed_at?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'assistant' => $this->assistant ? 'Assistant' : null,
            'user' => ($this->user_id) ? $this->user->name : null,
        ];
    }
}
