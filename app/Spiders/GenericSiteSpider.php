<?php

namespace App\Spiders;

use Generator;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;
use RoachPHP\Downloader\Middleware\ExecuteJavascriptMiddleware;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use RoachPHP\Support\Configurable;

class GenericSiteSpider extends BasicSpider
{
    use Configurable;

    public array $startUrls = [];

    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
    ];

    public array $spiderMiddleware = [

    ];

    public array $responseMiddleware = [
        ExecuteJavascriptMiddleware::class,
    ];

    public array $itemProcessors = [
        //
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    public int $concurrency = 2;

    public int $requestDelay = 1;

    /**
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $collection = $this->context['collection'];
        $title = str($response->getUri())->afterLast('/')->snake()->replace("\/", '_')->toString();
        $body = $response->getBody();
        $name = $title.'.html';
        $converter = new HtmlConverter(['strip_tags' => true]);
        $markdown = $converter->convert($body);
        $markdownName = $title.'.md';
        Storage::disk('collections')->put($collection->id.'/'.$name, $body);
        Storage::disk('collections')->put($collection->id.'/'.$markdownName, $markdown);

        yield $this->item([
            'body' => $body,
        ]);
    }
}
