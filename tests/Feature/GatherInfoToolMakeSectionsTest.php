<?php

namespace Feature;

use App\Models\Collection;
use App\Models\Document;
use App\Models\Message;
use App\Models\Report;
use LlmLaraHub\LlmDriver\Functions\GatherInfoToolMakeSections;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class GatherInfoToolMakeSectionsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_sections(): void
    {
        LlmDriverFacade::shouldReceive('getFunctionsForUi')->andReturn([]);
        LlmDriverFacade::shouldReceive('driver->completionPool')
            ->andReturn([
                CompletionResponse::from([
                    'content' => fake()->sentences(3, true),
                ]),
                CompletionResponse::from([
                    'content' => fake()->sentences(3, true),
                ]),
                CompletionResponse::from([
                    'content' => fake()->sentences(3, true),
                ]),
            ]);

        $collection = Collection::factory()->create();

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        $chat = \App\Models\Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
        ]);

        $prompts = [
            'foo bar',
            'foo bar',
            'foo bar',
        ];

        $report = Report::factory()->create([
            'message_id' => $message->id,
            'chat_id' => $chat->id,
        ]);

        (new GatherInfoToolMakeSections())->handle($prompts, $report, $document);

        //1 * 20
        $this->assertDatabaseCount('sections', 3);
    }
}
