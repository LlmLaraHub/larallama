<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Message;
use LlmLaraHub\LlmDriver\Functions\ParametersDto;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
use LlmLaraHub\LlmDriver\Functions\SummarizeCollection;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class SummarizeCollectionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_generate_function_as_array(): void
    {
        $RetrieveRelated = new \LlmLaraHub\LlmDriver\Functions\SummarizeCollection();

        $function = $RetrieveRelated->getFunction();

        $parameters = $function->parameters;

        $this->assertInstanceOf(ParametersDto::class, $parameters);
        $this->assertIsArray($parameters->properties);
        $this->assertInstanceOf(PropertyDto::class, $parameters->properties[0]);
    }

    public function test_gathers_all_content()
    {
        $dto = CompletionResponse::from([
            'content' => 'This is a summary of the content',
        ]);
        LlmDriverFacade::shouldReceive('driver->setToolType->completion')
            ->once()
            ->andReturn($dto);

        $collection = Collection::factory()->create();

        $chat = \App\Models\Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        \App\Models\Document::factory(3)->create([
            'collection_id' => $collection->id,
            'summary' => 'Foo bar',
        ]);

        $message = Message::factory()->create(
            [
                'chat_id' => $chat->id,
                'body' => 'Can you summarize all this content for me separating the content into 2 parts',
            ]
        );

        $results = (new SummarizeCollection())->handle($message);

        $this->assertInstanceOf(\LlmLaraHub\LlmDriver\Responses\FunctionResponse::class, $results);

        $this->assertNotEmpty($results->content);
    }
}
