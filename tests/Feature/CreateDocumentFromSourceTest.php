<?php

namespace Tests\Feature;

use App\Domains\Documents\TypesEnum;
use App\Domains\Sources\DocumentDto;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class CreateDocumentFromSourceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_makes_document(): void
    {
        Bus::fake();
        $source = Source::factory()->create();

        LlmDriverFacade::shouldReceive('driver->onQueue')->andReturn('default');

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn(CompletionResponse::from([
                'content' => get_fixture('test_block_of_text.txt', false),
            ]));

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_chunks', 0);

        $fixture = get_fixture('test_block_of_text.txt', false);
        $handle = new \App\Domains\Sources\CreateDocumentFromSource();
        $handle->handle(
            $source,
            $fixture,
        DocumentDto::from([
            'type' => TypesEnum::HTML,
            'link' => "foo.com",
            'title' => "Foo Bar",
            'subject' => "Foo Bar",
            'file_path' => "foo.com",
            'document_md5' => md5($fixture),
            'meta_data' => [],
        ]));

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_chunks', 17);
    }
}
