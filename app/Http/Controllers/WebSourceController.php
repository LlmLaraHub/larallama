<?php

namespace App\Http\Controllers;

use App\Domains\Sources\SourceTypeEnum;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\SourceResource;
use App\Models\Collection;
use App\Models\Source;

class WebSourceController extends Controller
{
    public function index(Collection $collection)
    {
        return inertia('Sources/WebSource/Index', [
            'collection' => $collection,
            'sources' => SourceResource::collection($collection->sources()->paginate(10)),
        ]);
    }

    public function create(Collection $collection)
    {
        return inertia('Sources/WebSource/Create', [
            'collection' => new CollectionResource($collection),
        ]);
    }

    public function store(Collection $collection)
    {

        $validated = request()->validate([
            'title' => 'required|string',
            'details' => 'required|string',
        ]);

        Source::create([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'collection_id' => $collection->id,
            'type' => SourceTypeEnum::WebSearchSource,
            'meta_data' => [
                'driver' => 'brave',
                'limit' => 5
            ],
        ]);

        request()->session()->flash('flash.banner', 'Web source added successfully');

        return to_route('collections.sources.index', $collection);
    }

    public function edit(Collection $collection, Source $source) {

        return inertia('Sources/WebSource/Edit', [
            'source' => $source,
            'collection' => new CollectionResource($source->collection),
        ]);
    }

    public function update(Collection $collection, Source $source) {

        $validated = request()->validate([
            'title' => 'required|string',
            'details' => 'required|string',
        ]);

        $source->update($validated);

        request()->session()->flash('flash.banner', "Updated");

        return back();
    }
}
