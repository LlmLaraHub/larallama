<?php

namespace Tests\Feature\Models;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Chat\ToolsDto;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Filter;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Facades\LlmLaraHub\LlmDriver\Orchestrate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = Message::factory()->create();

        $report = Report::factory()->create([
            'message_id' => $model->id,
        ]);

        $this->assertNotNull($model->body);
        $this->assertNotNull($model->report->id);
        $this->assertNotNull($model->chat->id);
        $this->assertInstanceOf(MetaDataDto::class, $model->meta_data);
        $this->assertNotNull($model->meta_data->date_range);
        $this->assertNotNull($model->tools);
        $this->assertInstanceOf(ToolsDto::class, $model->tools);

    }

    public function test_get_filter(): void
    {
        $filter = Filter::factory()->create();
        $model = Message::factory()->create([
            'meta_data' => MetaDataDto::from([
                'filter' => $filter->id,
            ]),
        ]);
        $this->assertNotNull($model->getFilter());
    }

    public function test_run(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        Orchestrate::shouldReceive('handle')->never();

        $firstResponse = CompletionResponse::from([
            'content' => 'test',
        ]);

        LlmDriverFacade::shouldReceive('driver->chat')->once()->andReturn($firstResponse);

        $message = Message::factory()->user()->create([
            'meta_data' => MetaDataDto::from([
                'tool' => 'completion',
            ]),
        ]);

        $message->run();

        $this->assertNotNull($message->refresh()->meta_data->driver);
    }

    public function test_rerun(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        Orchestrate::shouldReceive('handle')->never();

        $firstResponse = CompletionResponse::from([
            'content' => 'test',
        ]);

        LlmDriverFacade::shouldReceive('driver->chat')->once()->andReturn($firstResponse);

        $message = Message::factory()->user()->create([
            'chat_id' => $chat->id,
            'meta_data' => MetaDataDto::from([
                'tool' => 'completion',
            ]),
        ]);

        $messageAssistant = Message::factory()->assistant()->create([
            'chat_id' => $chat->id,
            'meta_data' => MetaDataDto::from([
                'tool' => 'completion',
            ]),
        ]);

        $messageAssistant->reRun();

        $this->assertDatabaseCount('messages', 2);
    }
}
