<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function show(Report $report)
    {
        return response()->json([
            'report' => new ReportResource($report),
        ]);
    }

    public function export(Report $report)
    {
        return Excel::download(new ReportExport($report), 'report.xlsx');
    }
}
