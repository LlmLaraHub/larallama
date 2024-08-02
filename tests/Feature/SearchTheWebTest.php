<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LlmLaraHub\LlmDriver\Functions\SearchTheWeb;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class SearchTheWebTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_search_the_web(): void
    {
        WebSearchFacade::shouldReceive('driver->search')
            ->once()->andReturn(
                \App\Domains\Sources\WebSearch\Response\SearchResponseDto::from([
                    'web' => [
                        WebResponseDto::from([
                            'url' => 'https://google.com',
                            'title' => 'Google',
                            'age' => 'January 1, 2023',
                            'description' => 'Google is a search engine that helps people find information on the internet.',
                            'meta_data' => '{"key":"value"}',
                            'thumbnail' => 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
                            'profile' => [],
                        ]),
                    ],
                    'videos' => [],
                ])
            );

        GetPage::shouldReceive('handle')->once()->andReturn('Foo bar');

        LlmDriverFacade::shouldReceive('driver->completion')->once()->andReturn(
            CompletionResponse::from([
                'content' => 'Foo bar',
            ])
        );

        $message = Message::factory()->create([
            'body' => 'Search the web for a topic',
            'meta_data' => MetaDataDto::from([
                'args' => [
                    'search_phrase' => 'test',
                ],
            ]),
        ]);

        $results = (new SearchTheWeb())->handle($message);

    }
}
