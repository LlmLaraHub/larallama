<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Messages\RoleEnum;
use App\Events\ChatUiUpdateEvent;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
use App\Models\User;
use Facades\App\Domains\Messages\SearchAndSummarizeChatRepo;
use Illuminate\Support\Facades\Event;
use LlmLaraHub\LlmDriver\Functions\SearchAndSummarize;
use LlmLaraHub\LlmDriver\Functions\StandardsChecker;
use LlmLaraHub\LlmDriver\Functions\SummarizeCollection;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Orchestrate;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use Mockery;
use Tests\TestCase;

class OrchestrateTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_summarize_function(): void
    {
        Event::fake();
        LlmDriverFacade::shouldReceive('driver->functionPromptChat')->once()->andReturn([
            [
                'name' => 'summarize_collection',
                'arguments' => [
                    'TLDR it for me',
                ],
            ],
        ]);

        LlmDriverFacade::shouldReceive('driver->chat')->never();

        SearchAndSummarizeChatRepo::shouldReceive('search')->never();

        $this->instance(
            'summarize_collection',
            Mockery::mock(SummarizeCollection::class, function ($mock) {
                $mock->shouldReceive('handle')
                    ->once()
                    ->andReturn(
                        FunctionResponse::from(
                            [
                                'content' => 'This is the summary of the collection',
                                'prompt' => 'TLDR it for me',
                            ])
                    );
            })
        );

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        $message = Message::factory()->user()->create([
            'tools' => [],
            'chat_id' => $chat->id,
            'meta_data' => MetaDataDto::from(
                [
                    'tool' => '',
                ]
            ),
        ]);

        $results = (new Orchestrate())->handle($chat, $message);

        Event::assertDispatched(ChatUiUpdateEvent::class);

        $this->assertEquals($results, 'This is the summary of the collection');

        $this->assertDatabaseCount('prompt_histories', 1);

        $this->assertCount(1, $message->tools->tools);

        $message = Message::where('chat_id', $chat->id)->where("role", RoleEnum::Assistant)->first();

        $this->assertNotNull($message?->id);

        $this->assertCount(1, $message->tools->tools);

        $this->assertEquals('summarize_collection', $message->tools->tools[0]->function_name);
    }

    public function test_tool_standards_checker(): void
    {
        Event::fake();

        $this->instance(
            'standards_checker',
            Mockery::mock(StandardsChecker::class, function (Mockery\MockInterface $mock) {
                $mock->shouldReceive('handle')
                    ->once()
                    ->andReturn(
                        FunctionResponse::from(
                            [
                                'content' => 'This is the summary of the collection',
                                'prompt' => 'TLDR it for me',
                            ])
                    );
            })
        );

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        $message = Message::factory()->user()->create([
            'meta_data' => MetaDataDto::from(
                [
                    'tool' => 'standards_checker',
                ]
            ),
        ]);

        $results = (new Orchestrate())->handle($chat, $message);

    }

    public function test_makes_history_no_message(): void
    {
        Event::fake();
        LlmDriverFacade::shouldReceive('driver->functionPromptChat')->once()->andReturn([
            [
                'name' => 'search_and_summarize',
                'arguments' => [
                    'TLDR it for me',
                ],
            ],
        ]);

        LlmDriverFacade::shouldReceive('driver->chat')->never();

        SearchAndSummarizeChatRepo::shouldReceive('search')->never();

        $this->instance(
            'search_and_summarize',
            Mockery::mock(SearchAndSummarize::class, function ($mock) {
                $mock->shouldReceive('handle')
                    ->once()
                    ->andReturn(
                        FunctionResponse::from(
                            [
                                'content' => 'This is the summary of the collection',
                                'prompt' => 'TLDR it for me',
                            ])
                    );
            })
        );

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        $message = Message::factory()->user()->create([
            'meta_data' => MetaDataDto::from(
                [
                    'tool' => null,
                ]
            ),
        ]);

        $results = (new Orchestrate())->handle($chat, $message);

        Event::assertDispatched(ChatUiUpdateEvent::class);

        $this->assertEquals($results, 'This is the summary of the collection');
    }
}
