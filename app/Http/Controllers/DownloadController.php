<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function download(Collection $collection)
    {
        $validate = request()->validate([
            'document_name' => 'required|string',
        ]);

        return Storage::disk('collections')
            ->download($collection->id.'/'.$validate['document_name']);
    }
}
