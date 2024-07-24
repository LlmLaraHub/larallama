<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Prompts\GoogleSheetSource;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Controllers\BaseSourceController;
use App\Models\Collection;
use App\Models\Source;
use Facades\App\Domains\Sources\GoogleSheetSource\GoogleSheetWrapper;

class GoogleSheetSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::GoogleSheetSource;

    protected string $edit_path = 'Sources/GoogleSheetSource/Edit';

    protected string $show_path = 'Sources/GoogleSheetSource/Show';

    protected string $create_path = 'Sources/GoogleSheetSource/Create';

    protected string $info = 'Add an URL that is Public Viewable and the system will keep an eye on it';

    protected string $type = 'Google Sheet Source';

    public function getPrompts(): array
    {
        return [
            'sheet_to_document' => GoogleSheetSource::prompt('[CONTEXT]'),
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
            'meta_data.sheet_id' => ['required', 'string'],
            'meta_data.sheet_name' => ['required', 'string'],
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
            'user_id' => $this->getUserId($collection),
            'type' => $this->sourceTypeEnum,
            'meta_data' => $validated['meta_data'],
        ]);
    }

    public function testFeed()
    {
        $validated = request()->validate([
            'sheet_id' => ['required', 'string'],
            'sheet_name' => ['required', 'string'],
        ]);

        $items = GoogleSheetWrapper::handle($validated['sheet_id'], $validated['sheet_name'], 'A1:Z10');

        $rows = array_map('str_getcsv', explode("\n", $items));

        return response()->json([
            'count' => count($rows),
            'items' => $rows,
        ]);
    }
}
