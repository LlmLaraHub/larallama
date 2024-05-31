<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Output;
use App\Models\User;
use Facades\LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;
use Tests\TestCase;

class WebPageOutputControllerTest extends TestCase
{
    public function test_show()
    {
        $output = Output::factory()->create([
            'active' => true,
            'public' => true,
        ]);

        $this->get(route(
            'collections.outputs.web_page.show', [
                'output' => $output->slug,
            ]
        ))->assertStatus(200);
    }

    public function test_chat_summarize()
    {
        $output = Output::factory()->create([
            'active' => true,
            'public' => true,
        ]);

        Document::factory()->create([
            'collection_id' => $output->collection_id,
        ]);

        DocumentChunk::factory()->create();

        DistanceQueryFacade::shouldReceive('cosineDistance')->never();

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->never();

        LlmDriverFacade::shouldReceive('driver->completion')
            ->twice()
            ->andReturn(CompletionResponse::from([
                'content' => 'summarize',
            ]));

        $this->post(route(
            'collections.outputs.web_page.chat', [
                'output' => $output->id,
            ]
        ), [
            'input' => 'Summarize the collection',
        ])->assertStatus(302);
    }

    public function test_chat_search()
    {
        $output = Output::factory()->create([
            'active' => true,
            'public' => true,
        ]);
        NonFunctionSearchOrSummarize::shouldReceive('handle')
            ->once()->andReturn(
                NonFunctionResponseDto::from([
                    'response' => 'Foobar',
                    'documentChunks' => collect(),
                    'prompt' => 'Foobar',
                ]));

        $this->post(route(
            'collections.outputs.web_page.chat', [
                'output' => $output->id,
            ]
        ), [
            'input' => 'Search for foo bar',
        ])->assertStatus(302);
    }

    public function test_no_search_no_summary()
    {
        NonFunctionSearchOrSummarize::shouldReceive('handle')
            ->once()->andReturn(
                NonFunctionResponseDto::from([
                    'response' => 'Foobar',
                    'documentChunks' => collect(),
                    'prompt' => 'Foobar',
                ])
            );

        $output = Output::factory()->create([
            'active' => true,
            'public' => true,
        ]);

        $this->post(route(
            'collections.outputs.web_page.chat', [
                'output' => $output->id,
            ]
        ), [
            'input' => 'Search for foo bar',
        ])->assertStatus(302);
    }

    public function test_show_not_public()
    {
        $output = Output::factory()->create([
            'active' => true,
            'public' => false,
        ]);

        $this->get(route(
            'collections.outputs.web_page.show', [
                'output' => $output->slug,
            ]
        ))->assertStatus(404);

    }

    public function test_show_not_public_but_authenticated()
    {
        $output = Output::factory()->create([
            'active' => true,
            'public' => false,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route(
                'collections.outputs.web_page.show', [
                    'output' => $output->slug,
                ]
            ))->assertStatus(200);
    }

    public function test_show_not_active()
    {
        $output = Output::factory()->create([
            'active' => false,
            'public' => true,
        ]);

        $this->get(route(
            'collections.outputs.web_page.show', [
                'output' => $output->slug,
            ]
        ))->assertStatus(404);

    }

    /**
     * A basic feature test example.
     */
    public function test_summary(): void
    {

        $user = User::factory()->create();

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()->andReturn(CompletionResponse::from([
                'content' => 'Test',
            ]));

        $collection = Collection::factory()->create();

        Document::factory(5)->create([
            'collection_id' => $collection->id,
        ]);

        $this->actingAs($user)->post(route(
            'collections.outputs.web_page.summary',
            [
                'collection' => $collection->id,
            ]
        )
        );

    }

    public function test_store(): void
    {

        $user = User::factory()->create();

        $collection = Collection::factory()->create();

        Document::factory(5)->create([
            'collection_id' => $collection->id,
        ]);

        $this->actingAs($user)->post(route(
            'collections.outputs.web_page.store',
            [
                'collection' => $collection->id,
            ]
        ), [
            'title' => 'Foobar',
            'summary' => 'Foobar',
            'active' => false,
            'public' => false,
        ]
        );

        $this->assertDatabaseCount('outputs', 1);

    }

    public function test_edit(): void
    {

        $output = Output::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)->get(route(
            'collections.outputs.web_page.edit',
            [
                'collection' => $output->collection_id,
                'output' => $output->id,
            ]
        )
        )->assertStatus(200);

    }

    public function test_update(): void
    {

        $webpage = Output::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)->put(route(
            'collections.outputs.web_page.update',
            [
                'collection' => $webpage->collection_id,
                'output' => $webpage->id,
            ]
        ), [
            'title' => 'Foobar2',
            'summary' => 'Foobar2',
            'active' => true,
            'public' => false,
        ]
        );

        $this->assertDatabaseCount('outputs', 1);

        $output = Output::first();

        $this->assertTrue($output->active);
        $this->assertFalse($output->public);
    }
}
