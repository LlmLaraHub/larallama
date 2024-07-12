<?php

namespace Feature;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Reporting\StatusEnum;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Message;
use App\Models\Report;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\Functions\ParametersDto;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
use LlmLaraHub\LlmDriver\Functions\ReportingTool;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class ReportingToolTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_generate_function_as_array(): void
    {
        $searchAndSummarize = new \LlmLaraHub\LlmDriver\Functions\ReportingTool();

        $function = $searchAndSummarize->getFunction();

        $parameters = $function->parameters;

        $this->assertInstanceOf(ParametersDto::class, $parameters);
        $this->assertIsArray($parameters->properties);
        $this->assertInstanceOf(PropertyDto::class, $parameters->properties[0]);
    }

    public function test_asks()
    {

        $content = <<<CONTENT
"Compare this content to the standards.
    Example Document

Overview: This document is going to show you how to configure the router and what steps you need to take. It\’s really simple, so just follow along. First, open the admin panel by entering the IP address into your browser. Then you need to go to the settings tab and configure your Wi-Fi settings. Click Save.

After you\’ve done that, you need to check if the configuration is correct. If you get an error, then something went wrong. Check the settings again or maybe restart the router. You should be good to go! Remember, a well-configured router is essential for a strong and reliable internet connection.
    "
CONTENT;

        $messageArray = [];

        $prompt = 'Can you check this document against the standards \n'.$content;

        $messageArray[] = MessageInDto::from([
            'content' => $prompt,
            'role' => 'user',
        ]);

        $dto1 = CompletionResponse::from([
            'content' => '[
    {
        "title": "[REQUEST 1 TITLE]",
        "content": "[REQUEST 1 CONTENT]"
    },
    {
        "title": "[REQUEST 2 TITLE]",
        "content": "[REQUEST 2 CONTENT]"
    }
]',
        ]);

        $dto2 = CompletionResponse::from([
            'content' => '[
    {
        "title": "[REQUEST 3 TITLE]",
        "content": "[REQUEST 3 CONTENT]"
    },
    {
        "title": "[REQUEST 4 TITLE]",
        "content": "[REQUEST 4 CONTENT]"
    }
]',
        ]);

        LlmDriverFacade::shouldReceive('driver->completionPool')
            ->times(5)
            ->andReturn([
                $dto1,
                $dto2,
            ]);

        LlmDriverFacade::shouldReceive('driver->completion')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

        $collection = Collection::factory()->create();

        Document::factory(5)
            ->has(DocumentChunk::factory(), 'document_chunks')
            ->create([
                'collection_id' => $collection->id,
            ]);

        $chat = \App\Models\Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $functionCallDto = \LlmLaraHub\LlmDriver\Functions\FunctionCallDto::from([
            'function_name' => 'reporting_tool',
            'arguments' => json_encode([
                'prompt' => $prompt,
            ]),
        ]);

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
        ]);

        $this->assertDatabaseCount('sections', 0);
        $results = (new ReportingTool())
            ->handle($message);

        $this->assertDatabaseCount('sections', 20);
        $this->assertInstanceOf(\LlmLaraHub\LlmDriver\Responses\FunctionResponse::class, $results);

        $this->assertNotEmpty($results->content);
    }

    public function test_builds_up_sections()
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

        $results = (new ReportingTool())
            ->handle($message);

        $this->assertInstanceOf(\LlmLaraHub\LlmDriver\Responses\FunctionResponse::class, $results);

        //1 * 20
        $this->assertDatabaseCount('sections', 26);
    }

    public function test_makes_entries()
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
            'meta_data' => MetaDataDto::from([
                'reference_collection_id' => $referenceCollection->id,
            ]),
        ]);

        $this->assertDatabaseCount('reports', 0);
        $results = (new ReportingTool())
            ->handle($message);

        $this->assertInstanceOf(\LlmLaraHub\LlmDriver\Responses\FunctionResponse::class, $results);

        //1 * 20
        $this->assertDatabaseCount('sections', 26);
        $this->assertDatabaseCount('entries', 26);
        $this->assertDatabaseCount('reports', 1);
        $report = Report::first();
        $this->assertEquals(StatusEnum::Complete, $report->status_sections_generation);
        $this->assertEquals(StatusEnum::Complete, $report->status_entries_generation);
    }
}
