<?php

namespace App\Http\Controllers;

use App\Domains\Collections\CollectionStatusEnum;
use App\Http\Resources\DocumentResource;
use App\Jobs\SummarizeDocumentJob;
use App\Models\Document;
use Illuminate\Http\Request;

class UpdateSummaryController extends Controller
{
    public function updateSummary(Document $document, Request $request)
    {

        notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSING, 'Document Summary updating');
        SummarizeDocumentJob::dispatchSync($document);
        notify_collection_ui($document->collection, CollectionStatusEnum::PROCESSED, 'Document Summary Complete');

        return response()->json(
            new DocumentResource($document->refresh())
        );
    }
}
