<?php

namespace Tests\Feature;

use Facades\App\Domains\Sources\WebSearch\GetPage;
use League\HTMLToMarkdown\HtmlConverter;
use Tests\TestCase;

class GetPageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_get_page(): void
    {
        $this->markTestSkipped('@TODO working on an idea');
        $url = 'https://alnutile.medium.com/multiple-openai-functions-php-laravel-466cb72eefb8';
        //$url = "https://alfrednutile.info/ssh-config";
        //$url = 'https://laravel-news.com/replicate-laravel-php-client';

        $results = GetPage::handle($url);
        $converter = new HtmlConverter(['strip_tags' => true]);
        $markdown = $converter->convert($results);
    }
}
