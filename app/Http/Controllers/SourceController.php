<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\SourceResource;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Source;

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

    public function run(Source $source)
    {
        $source->run();
        request()->session()->flash('flas.banner', 'Web source is running');

        return back();
    }
}
