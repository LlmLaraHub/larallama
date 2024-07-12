<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportResource;
use App\Models\Report;

class ReportController extends Controller
{
    public function show(Report $report)
    {
        return response()->json([
            'report' => new ReportResource($report),
        ]);
    }
}
