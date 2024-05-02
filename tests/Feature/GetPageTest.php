<?php

namespace Tests\Feature;

use App\Models\Collection;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Storage;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class GetPageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_get_page(): void
    {
        Storage::fake('collections');
        $this->markTestSkipped('@TODO mock browser shot');
        $url = 'https://alnutile.medium.com/multiple-openai-functions-php-laravel-466cb72eefb8';
        $url = 'https://alfrednutile.info/ssh-config';
        //$url = 'https://laravel-news.com/replicate-laravel-php-client';
        $html = get_fixture('test_blog.html', false);

        $results = GetPage::handle($url);
        //put_fixture('test_blog.html', $results, false);
    }

    public function test_iterator()
    {

        $html = get_fixture('test.html', false);
        $html = get_fixture('test_blog.html', false);

        $json = get_fixture('web_search_html_converted_to_json_ollama.json', false);

        $collection = Collection::factory()->create();

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn(
                CompletionResponse::from([
                    'content' => $json,
                ])
            );

        $data = get_fixture('json_fixed.json', false);

        LlmDriverFacade::shouldReceive('driver->completion')
            ->once()
            ->andReturn(
                CompletionResponse::from([
                    'content' => $data,
                ])
            );

        $results = GetPage::make($collection)->parseHtml($html);

        $this->assertNotEmpty($results);

    }
}
