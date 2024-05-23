<?php

namespace App\Http\Controllers;

use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\FilterResource;
use App\Http\Resources\SourceResource;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Source;

class BaseSourceController extends Controller
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::WebSearchSource;

    protected string $edit_path = 'Sources/WebSource/Edit';

    protected string $show_path = 'Sources/WebSource/Show';

    protected string $create_path = 'Sources/WebSource/Create';

    public function create(Collection $collection)
    {
        return inertia($this->create_path, [
            'recurring' => RecurringTypeEnum::selectOptions(),
            'collection' => new CollectionResource($collection),
        ]);
    }

    public function store(Collection $collection)
    {

        $validated = request()->validate([
            'title' => 'required|string',
            'details' => 'required|string',
            'active' => ['boolean', 'required'],
            'recurring' => ['string', 'required'],
        ]);

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
            'source' => $source,
            'recurring' => RecurringTypeEnum::selectOptions(),
            'collection' => new CollectionResource($source->collection),
        ]);
    }

    public function update(Collection $collection, Source $source)
    {

        $validated = request()->validate([
            'title' => 'required|string',
            'details' => 'required|string',
            'active' => ['boolean', 'required'],
            'recurring' => ['string', 'required'],
        ]);

        $source->update($validated);

        request()->session()->flash('flash.banner', 'Updated');

        return back();
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
            'available_sources' => [
                [
                    'route' => route('collections.sources.websearch.create',
                        [
                            'collection' => $collection->id,
                        ]
                    ),
                    'name' => 'Web Source',
                    'active' => true,
                ],
                [
                    'route' => route('collections.sources.email_source.create',
                        [
                            'collection' => $collection->id,
                        ]
                    ),
                    'name' => 'Assistant Email Box',
                    'active' => true,
                ],
            ],
            'sources' => SourceResource::collection($collection->sources()->paginate(10)),
        ]);
    }

    public function run(Source $source)
    {
        $source->run();
        request()->session()->flash('flash.banner', 'Source is running');

        return back();
    }
}
