<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Filter;

class FilterController extends Controller
{
    public function create(Collection $collection)
    {
        $validated = request()->validate([
            'documents' => ['required', 'array'],
            'name' => ['required'],
            'description' => ['nullable'],
        ]);

        $filter = Filter::create([
            'created_by_id' => auth()->user()->id,
            'collection_id' => $collection->id,
            'name' => $validated['name'],
            'description' => data_get($validated, 'description', null),
        ]);

        $filter->documents()->attach($validated['documents']);

        request()->session()->flash('flash.banner', 'Filter Created');

        return back();
    }

    public function delete(Filter $filter)
    {

        $filter->delete();

        request()->session()->flash('flash.banner', 'Filter Deleted');

        return back();
    }
}
