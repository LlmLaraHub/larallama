<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Http\Resources\EventResource;
use App\Models\Collection;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function show(Collection $collection)
    {
        $date = request()->input('date') ? Carbon::parse(request()->input('date')) : now();
        $startOfCalendar = $date->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        return inertia('Calendar/Show', [
            "collection" => new CollectionResource($collection),
            'events' => EventResource::collection(
                Event::where('collection_id', $collection->id)
                    ->whereBetween('start_date', [$startOfCalendar, $endOfCalendar])
                    ->get()
            ),
            'startDate' => $startOfCalendar->format('Y-m-d'),
            'endDate' => $endOfCalendar->format('Y-m-d'),
            'currentMonth' => $date->format('Y-m'),
        ]);
    }
}
