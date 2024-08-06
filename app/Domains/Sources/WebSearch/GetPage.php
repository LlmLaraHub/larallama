<?php

namespace App\Domains\Sources\WebSearch;

use App\Domains\WebParser\WebContentResultsDto;
use App\Models\Collection;
use App\Models\Setting;
use Facades\App\Domains\WebParser\DefaultClient;
use Facades\App\Domains\WebParser\FireCrawlClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\Converter\CodeConverter;
use League\HTMLToMarkdown\Converter\PreformattedConverter;
use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\Converter\TextConverter;
use League\HTMLToMarkdown\Environment;
use League\HTMLToMarkdown\HtmlConverter;

class GetPage
{
    public function __construct(public Collection $collection)
    {
    }

    public static function make(Collection $collection): self
    {
        /** @phpstan-ignore-next-line */
        return new static($collection);
    }

    public function handle(string $url, bool $parseHtml = true): WebContentResultsDto
    {
        $name = md5($url).'.pdf';
        /**
         * @TODO
         * Make this a driver like the rest of the system
         */
        if (Setting::getSecret('fire_crawl', 'api_key')) {
            Log::info('Using FireCrawl');
            $results = FireCrawlClient::scrape($url);
        } else {
            Log::info('Using Default Browsershot');
            /** @var WebContentResultsDto $results */
            $results = DefaultClient::scrape($url);
            Storage::disk('collections')->put($this->collection->id.'/'.$name, $results->browserShot->pdf());
        }

        return $results;
    }

    public function parseHtml(string $html): string
    {
        $environment = new Environment([
            'strip_tags' => true,
            'suppress_errors' => true,
            'hard_break' => true,
            'strip_placeholder_links' => true,
            'remove_nodes' => 'nav footer header script style meta',
        ]);
        $environment->addConverter(new TableConverter());
        $environment->addConverter(new CodeConverter());
        $environment->addConverter(new PreformattedConverter());
        $environment->addConverter(new TextConverter());

        $converter = new HtmlConverter($environment);

        $markdown = $converter->convert($html);

        return str($markdown)->trim()->toString();

    }
}
