<?php

namespace Tests\Feature;

use App\Jobs\SummarizeDocumentJob;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Document;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use Tests\TestCase;

class SummarizeDocumentJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_summary_job(): void
    {

        $data = 'Foo bar';
        $dto = new \LlmLaraHub\LlmDriver\Responses\CompletionResponse($data);

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn($dto);

        $collection = Collection::factory()->create();

        $chat = Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $document = Document::factory()->create([
            'summary' => null,
            'collection_id' => $collection->id,
        ]);

        $this->fakeVerify($chat);

        $job = new SummarizeDocumentJob($document);
        $job->handle();

        $this->assertEquals('verified yay!', $document->refresh()->summary);
    }
}
