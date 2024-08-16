<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Event;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class CreateEventTool extends FunctionContract
{
    use ToolsHelper;

    public bool $showInUi = false;

    public array $toolTypes = [
        ToolTypes::Source,
        ToolTypes::Output,
    ];

    protected string $name = 'create_event_tool';

    protected string $description = 'If the user needs to create an event this tool help';

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] CreateEventTool called');

        $args = $message->meta_data->args;

        $assigned_to_assistant = data_get($args, 'assigned_to_assistant', false);
        $description = data_get($args, 'description', null);
        $start_date = data_get($args, 'start_date', null);
        $start_time = data_get($args, 'start_time', null);
        $end_date = data_get($args, 'end_date', null);
        $end_time = data_get($args, 'end_time', null);
        $location = data_get($args, 'location', null);
        $type = data_get($args, 'type', 'event');
        $title = data_get($args, 'title', null);
        $all_day = data_get($args, 'all_day', false);

        if (! $title || ! $start_date) {
            throw new \Exception('No title found');
        }

        if ($start_date == null && $start_date != '') {
            $start_date = str($start_date)->remove('\\')->toString();
        } else {
            $start_date = null;
        }

        if ($end_date && $end_date !== '') {
            $end_date = str($end_date)->remove('\\')->toString();
        } else {
            $end_date = null;
        }

        $event = Event::create([
            'title' => $title,
            'description' => $description,
            'start_date' => $start_date,
            'start_time' => $start_time,
            'end_date' => $end_date,
            'end_time' => $end_time,
            'location' => $location,
            'type' => $type,
            'assigned_to_id' => null,
            'assigned_to_assistant' => $assigned_to_assistant,
            'all_day' => $all_day,
            'collection_id' => $message->getChatable()->id,
        ]);

        return FunctionResponse::from([
            'content' => $event->title,
            'prompt' => $message->getContent(),
            'requires_followup' => false,
            'documentChunks' => collect([]),
            'save_to_message' => false,
        ]);
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [];
    }

    public function runAsBatch(): bool
    {
        return false;
    }
}
