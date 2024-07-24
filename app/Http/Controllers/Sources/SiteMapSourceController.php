<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Prompts\SiteMapPrompt;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Controllers\BaseSourceController;
use App\Models\Collection;
use App\Models\Source;
use Facades\App\Domains\Sources\SiteMapSource\SiteMapParserWrapper;

class SiteMapSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::SiteMapSource;

    protected string $edit_path = 'Sources/SiteMapSource/Edit';

    protected string $show_path = 'Sources/SiteMapSource/Show';

    protected string $create_path = 'Sources/SiteMapSource/Create';

    protected string $info = 'Get sites from a sitemap.xml';

    protected string $type = 'Site Map Source';

    public function getPrompts(): array
    {
        return [
            'page_to_document' => SiteMapPrompt::prompt('[CONTEXT]'),
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
            'force' => ['nullable', 'boolean'],
        ];
    }

    protected function makeSource(array $validated, Collection $collection): void
    {
        Source::create([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'user_id' => $this->getUserId($collection),
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
            'force' => data_get($validated, 'force', false),
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

        $items = SiteMapParserWrapper::handle($validated['url']);

        return response()->json([
            'count' => count($items),
            'items' => $items->take(10)->toArray(),
        ]);
    }
}
