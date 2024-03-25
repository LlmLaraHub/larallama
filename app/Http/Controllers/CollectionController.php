<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    
    public function index() {

        return inertia("Collection/Index", [
            'collections' => CollectionResource::collection(Collection::query()
            ->where("team_id", auth()->user()->current_team_id)
            ->get())
        ]);
    }

    public function store()  {
        
        $validated = request()->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;

        $collection = Collection::create($validated);
        /**
         * Make and then reditect to the view page
         */
        request()->session()->flash("flash.banner", "Collection created successfully!");    

        return to_route('collections.show', $collection);
    }

    public function show(Collection $collection) {
        return inertia("Collection/Show", [
            'collection' => new CollectionResource($collection)
        ]);
    }
}
