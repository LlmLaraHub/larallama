<?php

namespace App\Http\Controllers;

use App\Http\Resources\SourceResource;
use App\Models\Collection;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    
    public function index(Collection $collection)
    {
        return inertia('Sources/Index', [
            'collection' => $collection,
            'sources' => SourceResource::collection($collection->sources()->paginate(10)),
        ]);
    }
}
