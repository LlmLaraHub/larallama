<?php

namespace App\Domains\Sources\WebSearch;

use App\Models\Collection;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;
use Spatie\Browsershot\Browsershot;

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

    public function handle(string $url): string
    {
        $results = Browsershot::url($url)
            ->dismissDialogs()
            ->fullPage();

        /**
         * @TODO this can repeat
         */
        $name = md5($url).'.pdf';

        Storage::disk('collections')->put($this->collection->id.'/'.$name, $results->pdf());

        return $results->bodyHtml();
    }

    public function parseHtml(string $html): string
    {
        $converter = new HtmlConverter(
            [
                'strip_tags' => true,
                'suppress_errors' => true,
                'hard_break' => true,
                'strip_placeholder_links' => true,
                'remove_nodes' => 'nav footer header script style meta',
            ]
        );

        $markdown = $converter->convert($html);

        return $markdown;
    }
}
