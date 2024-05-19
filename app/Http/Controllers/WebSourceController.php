<?php

namespace App\Http\Controllers;

use App\Domains\Sources\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\Source;

class WebSourceController extends Controller
{
    public function create(Collection $collection)
    {
        return inertia('Sources/WebSource/Create', [
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
            'recurring' => ['string', 'required']
        ]);

        Source::create([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
            'collection_id' => $collection->id,
            'type' => SourceTypeEnum::WebSearchSource,
            'meta_data' => [
                'driver' => 'brave',
                'limit' => 5,
            ],
        ]);

        request()->session()->flash('flash.banner', 'Web source added successfully');

        return to_route('collections.sources.index', $collection);
    }

    public function edit(Collection $collection, Source $source)
    {

        return inertia('Sources/WebSource/Edit', [
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
            'recurring' => ['string', 'required']
        ]);



        $source->update($validated);

        request()->session()->flash('flash.banner', 'Updated');

        return back();
    }
}
