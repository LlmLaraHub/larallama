<?php

namespace Tests\Feature;

use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Message;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\Functions\ParametersDto;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
use LlmLaraHub\LlmDriver\Functions\SearchAndSummarize;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use Tests\TestCase;

class SearchAndSummarizeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_generate_function_as_array(): void
    {
        $searchAndSummarize = new \LlmLaraHub\LlmDriver\Functions\SearchAndSummarize();

        $function = $searchAndSummarize->getFunction();

        $parameters = $function->parameters;

        $this->assertInstanceOf(ParametersDto::class, $parameters);
        $this->assertIsArray($parameters->properties);
        $this->assertInstanceOf(PropertyDto::class, $parameters->properties[0]);
    }

    public function test_gets_user_input()
    {
        $messageArray = [];

        $messageArray[] = MessageInDto::from([
            'content' => 'Can you summarize all this content for me',
            'role' => 'user',
        ]);

        $data = 'Foo bar';

        $dto = new \LlmLaraHub\LlmDriver\Responses\CompletionResponse($data);

        $functionCallDto = \LlmLaraHub\LlmDriver\Functions\FunctionCallDto::from([
            'function_name' => 'search_and_summarize',
            'arguments' => json_encode([
                'prompt' => 'search for foobar and summarize',
            ]),
        ]);

        LlmDriverFacade::shouldReceive('driver->chat')
            ->once()
            ->andReturn($dto);

        $embedding = get_fixture('embedding_response.json');

        $dto = \LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto::from([
            'embedding' => data_get($embedding, 'data.0.embedding'),
            'token_count' => 1000,
        ]);

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->once()
            ->andReturn($dto);

        $collection = Collection::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
        ]);

        $messageUser = Message::factory()->create([
            'body' => 'Results Before this one',
            'role' => RoleEnum::User,
            'is_chat_ignored' => false,
            'chat_id' => $chat->id,
        ]);

        $messageAssistant = Message::factory()->create([
            'body' => 'Results Before this one',
            'role' => RoleEnum::Assistant,
            'is_chat_ignored' => false,
            'chat_id' => $chat->id,
        ]);

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        $documentChunk = DocumentChunk::factory(3)->create([
            'document_id' => $document->id,
        ]);

        DistanceQueryFacade::shouldReceive('cosineDistance')
            ->once()
            ->andReturn(DocumentChunk::all());

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

        $results = (new SearchAndSummarize())->handle(
            messageArray: $messageArray,
            model: $chat,
            functionCallDto: $functionCallDto
        );

        $this->assertNotNull($results);
        $this->assertDatabaseCount('message_document_references', 3);
    }
}
