<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\OutputResource;
use App\Models\Collection;
use App\Models\Document;

class OutputController extends Controller
{
    public function index(Collection $collection)
    {
        $chatResource = $chatResource = $this->getChatResource($collection);

        return inertia('Outputs/Index', [
            'chat' => $chatResource,
            'collection' => new CollectionResource($collection),
            'documents' => DocumentResource::collection(Document::query()
                ->where('collection_id', $collection->id)
                ->latest('id')
                ->get()),
            'outputs' => OutputResource::collection($collection->outputs()->paginate(10)),
        ]);
    }
}
