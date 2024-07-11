<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class DeleteDocumentsController extends Controller
{
    public function delete()
    {
        $validated = \request()->validate([
            'documents' => ['required', 'array'],
        ]);

        Log::info('Deleting', [
            'ids' => $validated['documents'],
        ]);

        foreach ($validated['documents'] as $id) {
            $document = Document::with('document_chunks')->find($id);
            $document->document_chunks()->delete();

            $document->tags()->delete();
            $document->delete();
        }

        \request()->session()->flash('flash.banner', 'Deleted Documents');

        return back();
    }

    public function deleteAll(Collection $collection)
    {

        foreach ($collection->documents as $document) {
            $document->document_chunks()->delete();
            $document->tags()->delete();
            $document->delete();
        }

        request()->session()->flash('flash.banner', 'Deleted all Documents');

        return back();
    }
}
