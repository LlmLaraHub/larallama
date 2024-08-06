<?php

namespace Tests\Feature;

use App\Models\Collection;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;
use Tests\TestCase;

class GetPageTest extends TestCase
{


    public function test_iterator()
    {
        $html = get_fixture('test_blog.html', false);

        $collection = Collection::factory()->create();

        $results = GetPage::make($collection)->parseHtml($html);

        $this->assertNotEmpty($results);
    }

}
