<?php

namespace Tests\Feature;

use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Messages\SearchAndSummarizeChatRepo;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use Tests\TestCase;

class SearchAndSummarizeChatRepoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_search(): void
    {

        $data = 'Foo bar';
        $dto = new \LlmLaraHub\LlmDriver\Responses\CompletionResponse($data);

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

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        $documentChunk = DocumentChunk::factory(3)->create([
            'document_id' => $document->id,
        ]);

        DistanceQueryFacade::shouldReceive('cosineDistance')
            ->once()
            ->andReturn(DocumentChunk::all());

        $results = (new SearchAndSummarizeChatRepo())->search($chat, 'Puppy');

        $this->assertNotNull($results);
        $this->assertDatabaseCount('message_document_references', 3);
        $this->assertDatabaseCount('prompt_histories', 1);

    }
}
