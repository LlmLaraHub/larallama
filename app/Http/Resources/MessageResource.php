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
            'collection_id' => $this->chat->chatable_id,
            'body_markdown' => str($this->body)->markdown(),
            'diff_for_humans' => $this->created_at->diffForHumans(),
            'prompt_histories' => PromptHistoryResource::collection($this->prompt_histories),
            'prompt_histories_plain' => PromptHistoryResource::collection($this->prompt_histories),
            'message_document_references' => MessageDocumentReferenceResource::collection(
                $this->message_document_references()->orderBy('distance', 'asc')->limit(10)->get()),
        ];
    }
}
