<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Message;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class CreateTasksTool extends FunctionContract
{
    protected string $name = 'create_tasks_tool';

    protected string $description = 'If the Campaign needs to have tasks created or the users prompt requires it you can use this tool to make multiple tasks';

    public bool $showInUi = true;

    public array $toolTypes = [
        ToolTypes::Source,
        ToolTypes::Output,
        ToolTypes::Chat,
        ToolTypes::ChatCompletion,
    ];

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('TaskTool called');

        $args = $message->args;
        foreach (data_get($args, 'tasks', []) as $taskArg) {
            $name = data_get($taskArg, 'name', null);
            $details = data_get($taskArg, 'details', null);
            $due_date = data_get($taskArg, 'due_date', null);
            $assistant = data_get($taskArg, 'assistant', false);
            $user_id = data_get($taskArg, 'user_id', null);

            $project = $message->chat->getChatable();

            Task::updateOrCreate([
                'name' => $name,
                'project_id' => $project->id,
                'due_date' => $due_date,
            ],
                [
                    'details' => $details,
                    'assistant' => $assistant,
                    'user_id' => null, //@TODO coming back to this
                ]);
        }

        return FunctionResponse::from([
            'content' => json_encode($args),
        ]);
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'tasks',
                description: 'Array of task objects',
                type: 'array',
                required: true,
                properties: [
                    new PropertyDto(
                        name: 'items',
                        description: 'Task object',
                        type: 'object',
                        required: true,
                        properties: [
                            new PropertyDto(
                                name: 'name',
                                description: 'Name of the task',
                                type: 'string',
                                required: true
                            ),
                            new PropertyDto(
                                name: 'details',
                                description: 'Detailed info of the task',
                                type: 'string',
                                required: true
                            ),
                            new PropertyDto(
                                name: 'due_date',
                                description: 'Due date if any format "Y-m-d"',
                                type: 'string',
                                required: true
                            ),
                            new PropertyDto(
                                name: 'assistant',
                                description: 'Should the assistant be assigned this true or false',
                                type: 'string',
                            ),
                            new PropertyDto(
                                name: 'user_id',
                                description: 'User id if assigned to a user',
                                type: 'string',
                            ),
                        ]
                    ),
                ]
            ),
        ];
    }
}
