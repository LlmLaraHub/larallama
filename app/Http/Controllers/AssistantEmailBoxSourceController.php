<?php

namespace App\Http\Controllers;

use App\Domains\Prompts\EmailToDocumentSummary;
use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use App\Models\Source;
use Illuminate\Support\Str;

class AssistantEmailBoxSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::EmailSource;

    protected string $edit_path = 'Sources/EmailSource/Edit';

    protected string $show_path = 'Sources/EmailSource/Show';

    protected string $create_path = 'Sources/EmailSource/Create';

    protected string $info = 'This is an email box you can forward email to that LaraLamma will provide';

    protected string $type = 'Assistant Email Box Source';

    protected function makeSource(array $validated, Collection $collection): void
    {
        Source::create([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
            'collection_id' => $collection->id,
            'type' => $this->sourceTypeEnum,
            'slug' => str(Str::random(12))->remove('+')->toString(),
            'meta_data' => [],
        ]);
    }

    public function getPrompts(): array
    {
        return [
            'summarize_email' => EmailToDocumentSummary::prompt('[CONTEXT]'),
        ];
    }
}
