<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Prompts\FeedPrompt;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Controllers\BaseSourceController;
use App\Models\Collection;
use App\Models\Source;
use Facades\App\Domains\Sources\FeedSource;

class FeedSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::FeedSource;

    protected string $edit_path = 'Sources/FeedSource/Edit';

    protected string $show_path = 'Sources/FeedSource/Show';

    protected string $create_path = 'Sources/FeedSource/Create';

    protected string $info = 'Get Feeds from Websites and create content from them';

    protected string $type = 'Feed Source';

    public function getPrompts(): array
    {
        return [
            'page_to_document' => FeedPrompt::prompt('[CONTEXT]'),
        ];
    }

    protected function getValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'details' => 'required|string',
            'active' => ['boolean', 'required'],
            'recurring' => ['string', 'required'],
            'meta_data' => ['required', 'array'],
            'meta_data.feed_url' => ['required', 'string'],
            'secrets' => ['nullable', 'array'],
        ];
    }

    protected function makeSource(array $validated, Collection $collection): void
    {
        Source::create([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
            'collection_id' => $collection->id,
            'type' => $this->sourceTypeEnum,
            'meta_data' => $validated['meta_data'],
        ]);
    }

    public function testFeed()
    {
        $validated = request()->validate([
            'url' => 'required|string',
        ]);

        $items = FeedSource::getFeedFromUrl($validated['url']);

        return response()->json([
            'count' => count($items),
            'items' => $items,
        ]);
    }
}
