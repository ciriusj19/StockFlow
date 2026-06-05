<?php

namespace App\Http\Controllers;

use App\Exports\ArrayReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(ReportService $reportService): View
    {
        return view('reports.index', [
            'reports' => $reportService->summaries(),
        ]);
    }

    public function export(string $report, string $format, ReportService $reportService)
    {
        $data = $reportService->make($report);
        $filename = $data['filename'].'-'.today()->format('Y-m-d');

        return match ($format) {
            'pdf' => Pdf::loadView('reports.pdf', $data + ['generatedAt' => now()])
                ->setPaper('a4', 'landscape')
                ->download($filename.'.pdf'),
            'excel' => Excel::download(
                new ArrayReportExport($data['columns'], $data['rows']),
                $filename.'.xlsx',
            ),
            default => abort(404),
        };
    }
}
