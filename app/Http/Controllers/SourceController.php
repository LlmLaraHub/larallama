<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\SourceResource;
use App\Models\Collection;
use App\Models\Document;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    
    public function index(Collection $collection)
    {
        $chatResource = $chatResource = $this->getChatResource($collection);

        return inertia('Sources/Index', [
            'chat' => $chatResource,
            'collection' => new CollectionResource($collection),
            'documents' => DocumentResource::collection(Document::query()
                ->where('collection_id', $collection->id)
                ->latest('id')
                ->get()),
            'sources' => SourceResource::collection($collection->sources()->paginate(10)),
        ]);
    }
}
