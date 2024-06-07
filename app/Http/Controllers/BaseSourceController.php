<?php

namespace App\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\FilterResource;
use App\Http\Resources\SourceEditResource;
use App\Http\Resources\SourceResource;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Source;
use Illuminate\Support\Facades\Gate;

class BaseSourceController extends Controller
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::WebSearchSource;

    protected string $edit_path = 'Sources/WebSource/Edit';

    protected string $show_path = 'Sources/WebSource/Show';

    protected string $create_path = 'Sources/WebSource/Create';

    protected string $info = 'Your Source Info Here';

    protected string $type = 'Base Source';

    public function create(Collection $collection)
    {
        return inertia($this->create_path, [
            'recurring' => RecurringTypeEnum::selectOptions(),
            'info' => $this->info,
            'type' => $this->type,
            'prompts' => $this->getPrompts(),
            'collection' => new CollectionResource($collection),
        ]);
    }

    public function store(Collection $collection)
    {

        $validated = request()->validate($this->getValidationRules());

        $this->makeSource($validated, $collection);

        request()->session()->flash('flash.banner', 'Source added successfully');

        return to_route('collections.sources.index', $collection);
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
            'meta_data' => [
                'driver' => 'brave',
                'limit' => 5,
            ],
        ]);
    }

    public function edit(Collection $collection, Source $source)
    {

        return inertia($this->edit_path, [
            'source' => new SourceEditResource($source),
            'info' => $this->info,
            'prompts' => $this->getPrompts(),
            'type' => $this->type,
            'recurring' => RecurringTypeEnum::selectOptions(),
            'collection' => new CollectionResource($source->collection),
        ]);
    }

    public function update(Collection $collection, Source $source)
    {

        $validated = request()->validate($this->getValidationRules());

        $this->updateSource($source, $validated);

        request()->session()->flash('flash.banner', 'Updated');

        return back();
    }

    protected function updateSource(Source $source, array $validated): void
    {
        $source->update($validated);
    }

    public function index(Collection $collection)
    {
        $chatResource = $this->getChatResource($collection);

        return inertia('Sources/Index', [
            'chat' => $chatResource,
            'collection' => new CollectionResource($collection),
            'filters' => FilterResource::collection($collection->filters),
            'documents' => DocumentResource::collection(Document::query()
                ->where('collection_id', $collection->id)
                ->latest('id')
                ->get()),
            'available_sources' => SourceTypeEnum::getAvailableSources($collection),
            'sources' => SourceResource::collection($collection->sources()->orderBy('id')->paginate(10)),
        ]);
    }

    public function run(Source $source)
    {
        $source->run();
        request()->session()->flash('flash.banner', 'Source is running');

        return back();
    }

    public function delete(Source $source)
    {
        if (! Gate::allows('delete', $source)) {
            abort(403);
        }

        $collection = $source->collection;
        $source->delete();
        request()->session()->flash('flash.banner', 'Source deleted');

        return to_route('collections.sources.index', $collection);
    }

    public function getPrompts(): array
    {
        return [];
    }

    protected function getValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'details' => 'required|string',
            'active' => ['boolean', 'required'],
            'recurring' => ['string', 'required'],
            'meta_data' => ['nullable', 'array'],
            'secrets' => ['nullable', 'array'],
        ];
    }
}
