<?php

namespace LlmLaraHub\LlmDriver\Tests\Feature;

use App\Domains\Chat\DateRangesEnum;
use App\Domains\Chat\MetaDataDto;
use App\Models\Chat;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Filter;
use App\Models\Message;
use App\Models\Project;
use Illuminate\Support\Facades\File;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\DistanceQuery\Drivers\PostGres;
use LlmLaraHub\LlmDriver\Functions\CreateTasksTool;
use Pgvector\Laravel\Vector;
use Tests\TestCase;

class CreateTasksToolTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_generates_tasks(): void
    {
        $project = Project::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $project->id,
            'chatable_type' => Project::class,
        ]);

        $data = get_fixture('claude_chat_response.json');

        $data = data_get($data, 'tool_calls.1.arguments.tasks');

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'args' => [
                'tasks' => $data,
            ]
        ]);

        $this->assertDatabaseCount('tasks', 0);

        (new CreateTasksTool())->handle($message);

        $this->assertDatabaseCount('tasks', 5);

        $this->assertCount(5, $project->refresh()->tasks);

    }
}