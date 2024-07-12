<?php

namespace App\Http\Controllers;

use App\Http\Resources\SectionResource;
use App\Models\Report;
use App\Models\Section;

class SectionsController extends Controller
{
    public function index(Report $report)
    {
        $sections = Section::where('report_id', $report->id)->orderBy('sort_order')
            ->get();

        return response()->json([
            'sections' => SectionResource::collection($sections->load('entries')),
        ]);
    }
}
