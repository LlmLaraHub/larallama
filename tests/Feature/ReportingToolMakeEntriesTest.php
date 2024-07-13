<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Reporting\StatusEnum;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Message;
use App\Models\Report;
use App\Models\Section;
use Facades\LlmLaraHub\LlmDriver\Functions\ReportingToolMakeEntries;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class ReportingToolMakeEntriesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_entries(): void
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

        $referenceCollection = Collection::factory()->create();

        Document::factory(2)
            ->has(DocumentChunk::factory(33), 'document_chunks')
            ->create([
                'collection_id' => $referenceCollection->id,
            ]);

        DistanceQueryFacade::shouldReceive('cosineDistance')
            ->times(26)
            ->andReturn(DocumentChunk::limit(3)->get());

        $embedding = get_fixture('embedding_response.json');

        $dto = \LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto::from([
            'embedding' => data_get($embedding, 'data.0.embedding'),
            'token_count' => 1000,
        ]);

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->times(26)
            ->andReturn($dto);

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
            'meta_data' => MetaDataDto::from([
                'reference_collection_id' => $referenceCollection->id,
            ]),
        ]);

        $this->assertDatabaseCount('reports', 0);

        $report = Report::factory()->create([
            'message_id' => $message->id,
            'chat_id' => $chat->id,
        ]);

        Section::factory(26)->create([
            'report_id' => $report->id,
        ]);

        ReportingToolMakeEntries::handle($report);

        //1 * 20
        $this->assertDatabaseCount('entries', 24);
        $this->assertEquals(StatusEnum::Complete, $report->status_entries_generation);
    }
}
