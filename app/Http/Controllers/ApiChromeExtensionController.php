<?php

namespace App\Http\Controllers;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Collection;
use App\Models\Source;

class ApiChromeExtensionController extends Controller
{
    public function index()
    {
        $collections = Collection::orderBy('name')
            ->paginate(20);

        return response()->json(
            $collections
        );

    }

    public function createSource(Collection $collection)
    {
        $validated = request()->validate([
            'url' => 'required|string',
            'recurring' => 'required|string',
            'title' => 'required|string',
            'force' => 'required|boolean',
            'prompt' => 'required|string',
            'content' => 'required|string',
        ]);

        $source = Source::create([
            'title' => $validated['title'],
            'details' => $validated['prompt'],
            'recurring' => data_get($validated, 'recurring', 'not'),
            'active' => 1,
            'force' => data_get($validated, 'force', false),
            'collection_id' => $collection->id,
            'type' => SourceTypeEnum::WebPageSource,
            'user_id' => auth()->user()->id,
            'meta_data' => [
                'urls' => [
                    $validated['url'],
                ],
            ],
        ]);

        return response()->json('OK');
    }

    public function getSource(Collection $collection, Source $source)
    {
        return response()->json(
            [
                'id' => $source->id,
                'title' => $source->title,
                'prompt' => $source->details,
                'active' => $source->active,
                'recurring' => $source->recurring?->name,
                'force' => $source->force,
                'status' => 'non needed',
                'type' => $source->type,
                'collection_id' => $collection->id,
                'url' => data_get($source->meta_data, 'urls.0'),
            ]
        );
    }

    public function getSources()
    {
        $sources = Source::orderBy('id')
            ->whereType(SourceTypeEnum::WebPageSource)
            ->with('collection')
            ->paginate(20);

        return response()->json(
            $sources
        );
    }
}
