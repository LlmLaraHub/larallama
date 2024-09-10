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
        $user = [];
        if ($this->user_id) {
            $user = new UserResource($this->user);
        }

        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'from_ai' => $this->from_ai,
            'initials' => ($this->from_ai) ? 'Ai' : 'You',
            'type' => 'text', //@TODO
            'body' => $this->body,
            'user' => $user,
            'user_id' => $this->user_id,
            'content_raw' => $this->body,
            'collection_id' => $this->chat->chatable_id,
            'body_markdown' => str($this->body)->markdown(),
            'meta_data' => $this->meta_data,
            'tools' => $this->tools,
            'report' => new ReportSimpleResource($this->report),
            'diff_for_humans' => $this->created_at->diffForHumans(),
            'prompt_histories' => PromptHistoryResource::collection($this->prompt_histories),
            'prompt_histories_plain' => PromptHistoryResource::collection($this->prompt_histories),
            'message_document_references' => MessageDocumentReferenceResource::collection(
                $this->message_document_references()->orderBy('distance', 'asc')->limit(10)->get()),
        ];
    }
}
