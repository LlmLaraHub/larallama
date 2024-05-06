<?php

namespace Tests\Feature;

use App\Models\Collection;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;
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

        $results = GetPage::make($collection)->parseHtml($html);

        $this->assertNotEmpty($results);

    }

    public function test_ideas_for_markdown(): void
    {
        //Storage::fake('collections');
        $this->markTestSkipped('@TODO mock browser shot');
        $html = get_fixture('test.html', false);
        $html = get_fixture('test_blog.html', false);

        $markdown = str($html)->markdown()->toString();

        $converter = new HtmlConverter(
            [
                'strip_tags' => true,
                'suppress_errors' => true,
                'hard_break' => true,
                'strip_placeholder_links' => true,
                'remove_nodes' => 'footer header script style meta',
            ]
        );

        $markdown = $converter->convert($html);
    }
}
