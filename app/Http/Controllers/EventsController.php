<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Http\Resources\EventResource;
use App\Models\Collection;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class EventsController extends Controller
{
    public function index(Collection $collection)
    {
        $startMonth = now()->startOfMonth();
        $endMonth = now()->endOfMonth();

        if (request()->has('start')) {
            $startMonth = Carbon::parse(request()->get('start'));
            $endMonth = Carbon::parse(request()->get('end'));
        }

        Log::info('EventsController', [
            'start' => $startMonth,
            'end' => $endMonth,
        ]);

        $events = EventResource::collection(Event::where(
            'collection_id', $collection->id
        )
            ->whereBetween(
                'start_date', [$startMonth, $endMonth]
            )
            ->get());

        return Inertia::render('Events/Index', [
            'today' => $startMonth,
            'events' => $events,
            'collection' => new CollectionResource($collection),
        ]);
    }
}
