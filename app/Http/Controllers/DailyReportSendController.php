<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Facades\App\Domains\Projects\DailyReportService;

class DailyReportSendController extends Controller
{
    public function __invoke(Project $project)
    {
        DailyReportService::sendReport($project);
        \request()->session()->flash('flash.banner', 'Sent!');

        return back();
    }
}
