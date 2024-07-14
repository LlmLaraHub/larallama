<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Message;
use App\Models\Report;
use LlmLaraHub\LlmDriver\Functions\Reports\SummarizeReport;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class SummarizeReportTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_makes_summary(): void
    {
        LlmDriverFacade::shouldReceive('driver->completion')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

        $referenceCollection = Collection::factory()->create();

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
            'body' => 'bar',
            'meta_data' => MetaDataDto::from([
                'reference_collection_id' => $referenceCollection->id,
            ]),
        ]);

        $report = Report::factory()->create([
            'message_id' => $message->id,
            'chat_id' => $chat->id,
        ]);
        (new SummarizeReport())->handle($report);

        $message = Message::where('body', 'foo bar')->first();
        $this->assertNotNull($message);
    }
}
