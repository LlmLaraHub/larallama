<?php

namespace App\Domains\Sources\WebSearch;

use App\Models\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Spatie\Browsershot\Browsershot;
use SundanceSolutions\LarachainTokenCount\Facades\LarachainTokenCount;

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

        $name = str($url)->afterLast('/')->toString().'.pdf';

        Storage::disk('collections')->put($this->collection->id.'/'.$name, $results->pdf());

        return $results->bodyHtml();
    }

    public function parseHtml(string $html): array|string
    {
        $tokenCount = LarachainTokenCount::count($html);

        Log::info("[LaraChain] Token Count of html: $tokenCount");

        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);

        $html = str($html)->after('<body')->beforeLast('</body>')->toString();

        $tokenCountAfter = LarachainTokenCount::count($html);

        Log::info("[LaraChain] Token Count After of html: $tokenCountAfter");

        $prompt = <<<PROMPT
I need you to convert this HTML To JSON.
The HTML below needs to be an array of objects.
Each object will have a key type and the key content.
The key type would have the type of data eg title, narrative, image, video, etc.
Ignore footer, header, ads, etc.
The content would have the related content.
ONLY RETURN JSON NO INTRO TEXT ETC.
No Comments like "Here is the JSON array of objects" I am going to pass it to json_decode in PHP.
So the response would be:

[
    {
"type": "title",
"content": "The title of the page"
    },
    {
"type": "narrative",
"content": "The narrative of the page"
    },
    {
"type": "image",
"content": "full_url_path_to_image"
    },
    {
"type": "video",
"content": "url_path_to_video"
    }
]

### END EXAMPLE

### START HTML

$html

### END HTML



PROMPT;

        Log::info('[LaraChain] Prompt for HTML to JSON: ', [$prompt]);

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::driver($this->collection->getDriver())
            ->completion($prompt);

        $content = $results->content;

        $prompt = <<<PROMPT
As a JSON verification assistant you will review this 
encoded json, clean it up and turn it back as 
encoded json that the next line of code will pass to 
json_decode in PHP so it has to be valid.
Do not comment on it, do not tell me what you did
Do not add fluff like "Here is the cleaned up JSON"
Just return the cleaned up JSON.

```json
$content 
```
PROMPT;

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::driver($this->collection->getDriver())
            ->completion($prompt);

        Log::info('[LaraChain] Results from json converion: ', [$results->content]);

        return json_decode($results->content, true);
    }
}
