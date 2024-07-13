<?php

namespace App\Exports;

use App\Models\Report;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromCollection, ShouldAutoSize, WithColumnWidths, WithHeadings
{
    use Exportable;

    public function __construct(public Report $report)
    {
    }

    public function collection(): Collection
    {
        return DB::table('reports')
            ->select('sections.content as Requirement', 'entries.title as Answer', 'sections.sort_order as Sort Order')
            ->join('sections', 'reports.id', '=', 'sections.report_id')
            ->join('entries', 'sections.id', '=', 'entries.section_id')
            ->where('reports.id', $this->report->id)
            ->orderBy('sections.sort_order', 'asc')
            ->get();
    }

    public function columnWidths(): array
    {
        return [
            'A' => 100,
            'B' => 100,
        ];
    }

    public function headings(): array
    {
        return [
            ['Requirement', 'Solution', 'Sort Order'],
        ];
    }
}
