<?php

namespace App\Http\Controllers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Events\CollectionStatusEvent;
use App\Jobs\KickOffWebSearchCreationJob;
use App\Models\Collection;
use App\Models\Document;
use Illuminate\Http\Request;

class SearchSourceController extends Controller
{
    

    public function store(Collection $collection) {
        $validated = request()->validate([
            'content' => 'required|string',
            'name' => 'required|string',
        ]);

        $document = Document::create([
            'file_path' => $validated['name'],
            'collection_id' => $collection->id,
            'type' => TypesEnum::HTML,
            'summary' => StatusEnum::Pending,
            'status_summary' => StatusEnum::Pending,
        ]);

        KickOffWebSearchCreationJob::dispatch($document);
        
        CollectionStatusEvent::dispatch($document->collection, CollectionStatusEnum::PROCESSING);
        
        request()->session()->flash('flash.banner', 'Starting initial Search pages will show shortly');

        return back();
    }
}
