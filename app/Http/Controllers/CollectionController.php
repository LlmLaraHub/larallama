<?php

namespace App\Http\Controllers;

use App\Domains\Documents\TypesEnum;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Jobs\ParsePdfFileJob;
use App\Models\Collection;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class CollectionController extends Controller
{
    public function index()
    {

        return inertia('Collection/Index', [
            'collections' => CollectionResource::collection(Collection::query()
                ->where('team_id', auth()->user()->current_team_id)
                ->get()),

        ]);
    }

    public function store()
    {

        $validated = request()->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;

        $collection = Collection::create($validated);
        /**
         * Make and then reditect to the view page
         */
        request()->session()->flash('flash.banner', 'Collection created successfully!');

        return to_route('collections.show', $collection);
    }

    public function show(Collection $collection)
    {
        return inertia('Collection/Show', [
            'collection' => new CollectionResource($collection),
            'documents' => DocumentResource::collection(Document::query()
                ->where('collection_id', $collection->id)
                ->latest('id')
                ->get()),
        ]);
    }

    public function filesUpload(Collection $collection)
    {
        $validated = request()->validate([
            'files' => 'required',
        ]);

        foreach ($validated['files'] as $file) {
            Log::info('file info', [
                'name' => $file->getClientOriginalName(),
                'mimetype' => $file->getMimeType(),
            ]);

            $document = Document::create([
                'collection_id' => $collection->id,
                'file_path' => $file->getClientOriginalName(),
                'type' => TypesEnum::PDF,
            ]);

            $file->storeAs(
                path: $collection->id,
                name: $file->getClientOriginalName(),
                options: ['disk' => 'collections']
            );

            ParsePdfFileJob::dispatch($document);
        }

        request()->session()->flash('flash.banner', 'Files uploaded successfully!');

        return back();
    }
}
