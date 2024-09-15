<?php

namespace App\Http\Controllers;

use App\Domains\Outputs\OutputTypeEnum;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\EventResource;
use App\Models\Collection;
use App\Models\Event;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function show(Collection $collection)
    {
        //for now if there is no related output we do a 404
        if (! $collection->outputs()
            ->active()
            ->where('type', OutputTypeEnum::CalendarOutput)->first()) {
            abort(404);
        }

        // Parse the date from the query string, or use the current date if not provided
        $date = request()->input('date') ? Carbon::parse(request()->input('date')) : now();

        // Calculate the start and end of the calendar view
        $startOfCalendar = $date->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // Fetch events for the calculated date range
        $events = Event::where('collection_id', $collection->id)
            ->whereBetween('start_date', [$startOfCalendar, $endOfCalendar])
            ->get();

        return inertia('Calendar/Show', [
            'collection' => new CollectionResource($collection),
            'events' => EventResource::collection($events),
            'startDate' => $startOfCalendar->format('Y-m-d'),
            'endDate' => $endOfCalendar->format('Y-m-d'),
            'currentMonth' => $date->format('Y-m'),
        ]);
    }
}
