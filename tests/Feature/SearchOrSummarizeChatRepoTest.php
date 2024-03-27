<?php

namespace Tests\Feature;

use App\Domains\Messages\SearchOrSummarizeChatRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\LlmDriver\LlmDriverFacade;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;

class SearchOrSummarizeChatRepoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_search(): void
    {

        $data = 'Foo bar';
        $dto = new \App\LlmDriver\Responses\CompletionResponse($data);

        LlmDriverFacade::shouldReceive('driver->chat')
            ->once()
            ->andReturn($dto);

        $embedding = get_fixture('embedding_response.json');

        $dto = \App\LlmDriver\Responses\EmbeddingsResponseDto::from([
            'embedding' => data_get($embedding, 'data.0.embedding'),
            'token_count' => 1000
        ]);

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->once()
            ->andReturn($dto);

        $collection = Collection::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
        ]);

        $document = Document::factory()->create([
            'collection_id' => $collection->id,
        ]);

        $documentChunk = DocumentChunk::factory(3)->create([
            'document_id' => $document->id,
        ]);

        $results = (new SearchOrSummarizeChatRepo())->search($chat, 'Puppy');

        $this->assertNotNull($results);

        
    }
}
