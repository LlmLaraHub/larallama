<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Message;
use App\Models\Report;
use Facades\LlmLaraHub\LlmDriver\Functions\ReportingToolMakeSections;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class ReportingToolMakeSectionsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_sections(): void
    {
        $messageArray = [];

        $dtos = [];
        foreach (range(0, 13) as $i) {
            $data = fake()->sentences(3, true);
            $title = fake()->sentences(1, true);
            $data2 = fake()->sentences(3, true);
            $title2 = fake()->sentences(1, true);
            $content = <<<CONTENT
[
    {
        "title": "$title",
        "content": "$data"
    },
    {
        "title": "$title2",
        "content": "$data2"
    }
]
CONTENT;

            $dtos[] = CompletionResponse::from([
                'content' => $content,
            ]);
        }

        LlmDriverFacade::shouldReceive('getFunctionsForUi')->andReturn([]);
        LlmDriverFacade::shouldReceive('driver->completionPool')
            ->andReturn([
                $dtos[0],
                $dtos[1],
                $dtos[2],
            ], [
                $dtos[3],
                $dtos[4],
                $dtos[5],
            ],
                [
                    $dtos[6],
                    $dtos[7],
                    $dtos[8],
                ], [
                    $dtos[9],
                    $dtos[10],
                    $dtos[11],
                ],
                [
                    $dtos[12],
                ],
                [
                    $dtos[9],
                    $dtos[10],
                    $dtos[11],
                ], [
                    $dtos[9],
                    $dtos[10],
                    $dtos[11],
                ]);

        LlmDriverFacade::shouldReceive('driver->completion')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

        $collection = Collection::factory()->create();

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        foreach (range(0, 13) as $page) {
            foreach (range(0, 3) as $chunk) {
                DocumentChunk::factory()->create([
                    'document_id' => $document->id,
                    'sort_order' => $page,
                    'section_number' => $chunk,
                ]);
            }
        }

        $chat = \App\Models\Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $functionCallDto = \LlmLaraHub\LlmDriver\Functions\FunctionCallDto::from([
            'function_name' => 'reporting_tool',
            'arguments' => json_encode([
                'prompt' => 'foo bar',
            ]),
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

        ReportingToolMakeSections::handle($prompts, $report, $document);

        //1 * 20
        $this->assertDatabaseCount('sections', 6);
    }
}
