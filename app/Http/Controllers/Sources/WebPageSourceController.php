<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Prompts\WebPagePrompt;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Controllers\BaseSourceController;
use App\Models\Collection;
use App\Models\Source;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebPageSourceController extends BaseSourceController
{

    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::WebPageSource;

    protected string $edit_path = 'Sources/WebPageSource/Edit';

    protected string $show_path = 'Sources/WebPageSource/Show';

    protected string $create_path = 'Sources/WebPageSource/Create';

    protected string $info = 'Using a URL it will get the page for you';

    protected string $type = 'Web Page Source';

    protected function getValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'details' => 'required|string',
            'active' => ['boolean', 'required'],
            'recurring' => ['string', 'required'],
            'meta_data.urls' => ['required', 'string'],
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


    public function getPrompts(): array {
        return [
            'web_page' => WebPagePrompt::prompt('[CONTEXT]'),
        ];
    }

}
