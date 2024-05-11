<?php

namespace Tests\Feature;

use App\Domains\Agents\VerifyPromptOutputDto;
use App\Models\Collection;
use App\Models\DocumentChunk;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use LlmLaraHub\LlmDriver\Functions\ParametersDto;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
use LlmLaraHub\LlmDriver\Functions\SummarizeCollection;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class SummarizeCollectionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_generate_function_as_array(): void
    {
        $searchAndSummarize = new \LlmLaraHub\LlmDriver\Functions\SummarizeCollection();

        $function = $searchAndSummarize->getFunction();

        $parameters = $function->parameters;

        $this->assertInstanceOf(ParametersDto::class, $parameters);
        $this->assertIsArray($parameters->properties);
        $this->assertInstanceOf(PropertyDto::class, $parameters->properties[0]);
    }

    public function test_gathers_all_content()
    {
        $searchAndSummarize = new \LlmLaraHub\LlmDriver\Functions\SummarizeCollection();
        $messageArray = [];

        $messageArray[] = MessageInDto::from([
            'content' => 'Can you summarize all this content for me',
            'role' => 'user',
        ]);

        $dto = CompletionResponse::from([
            'content' => 'This is a summary of the content',
        ]);
        LlmDriverFacade::shouldReceive('driver->chat')
            ->once()
            ->andReturn($dto);

        $collection = Collection::factory()->create();

        $chat = \App\Models\Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        VerifyResponseAgent::shouldReceive('verify')->never()->andReturn(
            VerifyPromptOutputDto::from(
                [
                    'chattable' => $chat,
                    'originalPrompt' => 'test',
                    'context' => 'test',
                    'llmResponse' => 'test',
                    'verifyPrompt' => 'This is a completion so the users prompt was past directly to the llm with all the context.',
                    'response' => 'verified yay!',
                ]
            ));

        $document = \App\Models\Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        DocumentChunk::factory(3)->create(
            [
                'document_id' => $document->id,
            ]
        );

        $functionCallDto = \LlmLaraHub\LlmDriver\Functions\FunctionCallDto::from([
            'function_name' => 'summarize_collection',
            'arguments' => json_encode([
                'prompt' => 'Can you summarize all this content for me',
            ]),
        ]);

        $results = (new SummarizeCollection())->handle($messageArray, $chat, $functionCallDto);

        $this->assertInstanceOf(\LlmLaraHub\LlmDriver\Responses\FunctionResponse::class, $results);

        $this->assertNotEmpty($results->content);
    }
}
