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

    protected string $description = 'If the user needs to create one or more events this tool help';

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] CreateEventTool called');

        $args = $message->meta_data->args;

        $eventArray = data_get($args, 'events', []);

        foreach ($eventArray as $event) {
            $assigned_to_assistant = data_get($event, 'assigned_to_assistant', false);
            $description = data_get($event, 'description', null);
            $start_time = data_get($event, 'start_time', null);
            $end_time = data_get($event, 'end_time', null);
            $location = data_get($event, 'location', null);
            $type = data_get($event, 'type', 'event');
            $title = data_get($event, 'title', 'No Title Found');
            $all_day = data_get($event, 'all_day', false);

            Event::updateOrCreate([
                'title' => $title,
                'start_date' => $start_time,
                'location' => $location,
                'collection_id' => $message->getChatable()->id,
            ],
                [
                    'description' => $description,
                    'end_date' => $end_time,
                    'type' => $type,
                    'assigned_to_id' => null,
                    'assigned_to_assistant' => $assigned_to_assistant,
                    'all_day' => $all_day,
                ]);
        }

        return FunctionResponse::from([
            'content' => json_encode($eventArray),
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
        return [
            new PropertyDto(
                name: 'events',
                description: 'Array of event objects',
                type: 'array',
                required: true,
                properties: [
                    new PropertyDto(
                        name: 'items',
                        description: 'Event object',
                        type: 'object',
                        required: true,
                        properties: [
                            new PropertyDto(
                                name: 'start_time',
                                description: 'Start date and time of the event using format "Y-m-d H:i"',
                                type: 'string',
                                required: true
                            ),
                            new PropertyDto(
                                name: 'end_time',
                                description: 'End date and time of the event using format "Y-m-d H:i"',
                                type: 'string',
                                required: false
                            ),
                            new PropertyDto(
                                name: 'title',
                                description: 'Title of the event',
                                type: 'string',
                                required: true
                            ),
                            new PropertyDto(
                                name: 'location',
                                description: 'Location of the event',
                                type: 'string',
                                required: false
                            ),
                            new PropertyDto(
                                name: 'description',
                                description: 'Description of the event',
                                type: 'string',
                                required: true
                            ),
                        ]
                    ),
                ]
            ),
        ];
    }

    public function runAsBatch(): bool
    {
        return false;
    }
}
