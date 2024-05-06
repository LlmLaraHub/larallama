<?php

namespace App\Http\Controllers;

use App\Http\Resources\SourceResource;
use App\Models\Collection;

class WebSourceController extends Controller
{
    public function index(Collection $collection)
    {
        return inertia('Sources/WebSource/Index', [
            'collection' => $collection,
            'sources' => SourceResource::collection($collection->sources()->paginate(10)),
        ]);
    }
}
