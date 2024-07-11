<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResource;
use App\Http\Resources\DocumentResourceWithPaginate;
use App\Models\Collection;

class DocumentController extends Controller
{
    public function index(Collection $collection)
    {

        $filter = request()->get('filter');

        if ($filter) {
            $documents = $collection->documents()
                ->where('status', '=', $filter)->paginate(100);
        } else {
            $documents = $collection->documents()->paginate(100);
        }

        return response()->json([
            'documents' => new DocumentResourceWithPaginate($documents),
        ]);
    }

    public function status(Collection $collection)
    {

        return response()->json([
            'documents' => DocumentResource::collection($collection->documents),
        ]);
    }
}
