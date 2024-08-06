<?php

namespace App\Domains\WebParser;

use League\HTMLToMarkdown\Converter\CodeConverter;
use League\HTMLToMarkdown\Converter\PreformattedConverter;
use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\Converter\TextConverter;
use League\HTMLToMarkdown\Environment;
use League\HTMLToMarkdown\HtmlConverter;
use LlmLaraHub\LlmDriver\BaseClient;
use Spatie\Browsershot\Browsershot;

class DefaultClient extends BaseClient
{
    public function scrape(string $url): WebContentResultsDto
    {
        $results = Browsershot::url($url)
            ->userAgent('DailyAI Studio Browser 1.0, helping users automate workflows')
            ->dismissDialogs()
            ->fullPage();

        $plainResults = $this->parseHtml($results->bodyHtml());

        return WebContentResultsDto::from([
            'title' => str($plainResults)->limit(128)->title()->toString(),
            'description' => str($plainResults)->limit(256)->title()->toString(),
            'content' => $plainResults,
            'content_raw' => $results->bodyHtml(),
            'url' => $url,
            'browserShot' => $results,
        ]);
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
