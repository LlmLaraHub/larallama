<?php

namespace LlmLaraHub\LlmDriver\Requests;

use App\Domains\Chat\MetaDataDto;
use App\Models\Message;
use Spatie\LaravelData\Data;

class MessageInDto extends Data
{
    public function __construct(
        public mixed $content,
        public string $role,
        public bool $is_ai = false,
        public bool $show = true,
        public mixed $tool = '',
        public mixed $tool_id = '',
        public array $args = [],
        public ?MetaDataDto $meta_data = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'role' => $this->role,
            'tool_id' => $this->tool_id,
            'tool' => $this->tool,
            'args' => $this->args,
        ];
    }

    public static function fromMessageAsUser(Message $message): self
    {
        return MessageInDto::from(
            [
                'content' => $message->body,
                'role' => $message->role->value,
                'meta_data' => $message->meta_data,
                'is_ai' => false,
                'show' => true,
            ]
        );
    }

    public static function fromMessageAsAssistant(Message $message): self
    {
        return MessageInDto::from(
            [
                'content' => $message->body,
                'role' => $message->role->value,
                'meta_data' => $message->meta_data,
                'tool_id' => $message->meta_data?->tool_id,
                'tool' => $message->meta_data?->tool,
                'is_ai' => true,
                'show' => true,
            ]
        );
    }
}
