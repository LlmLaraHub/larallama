<?php

namespace Feature;

use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Message;
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
            ->times(2)
            ->andReturn([
                $dto1,
                $dto2,
            ]);

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

        $this->assertDatabaseCount('sections', 4);
        $this->assertInstanceOf(\LlmLaraHub\LlmDriver\Responses\FunctionResponse::class, $results);

        $this->assertNotEmpty($results->content);
    }

    public function test_builds_up_sections()
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

        $dto = CompletionResponse::from([
            'content' => 'Reply 1 2 and 3',
        ]);
        LlmDriverFacade::shouldReceive('getFunctionsForUi')->andReturn([]);
        LlmDriverFacade::shouldReceive('driver->completionPool')
            ->times(2)
            ->andReturn([
                $dto,
                $dto,
                $dto,
            ]);

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

        $results = (new ReportingTool())
            ->handle($message);

        $this->assertInstanceOf(\LlmLaraHub\LlmDriver\Responses\FunctionResponse::class, $results);
    }
}
