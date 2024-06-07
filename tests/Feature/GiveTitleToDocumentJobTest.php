<?php

namespace Tests\Feature;

use App\Jobs\GiveTitleToDocumentJob;
use App\Models\Document;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class GiveTitleToDocumentJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gives_title(): void
    {
        $document = Document::factory()->create([
            'subject' => null,
        ]);
        LlmDriverFacade::shouldReceive('driver->completion')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'foo bar',
            ])
        );

        $job = new GiveTitleToDocumentJob($document);
        $job->handle();

        $this->assertEquals($document->refresh()->subject, str('foo bar')->headline()->toString());
    }
}
