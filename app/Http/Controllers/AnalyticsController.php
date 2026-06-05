<?php

namespace App\Http\Controllers;

use App\Exports\ArrayReportExport;
use App\Models\AnalyticsRun;
use App\Services\AnalyticsCompilationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AnalyticsController extends Controller
{
    public function index(AnalyticsCompilationService $analytics): View
    {
        return view('analytics.index', $analytics->dashboardData($analytics->currentRun()));
    }

    public function compile(Request $request, AnalyticsCompilationService $analytics): RedirectResponse
    {
        $analytics->compile($request->user());

        return redirect()
            ->route('analytics.index')
            ->with('success', 'Synthèse décisionnelle compilée.');
    }

    public function export(string $format, AnalyticsCompilationService $analytics)
    {
        $run = $analytics->currentRun();

        abort_unless($run instanceof AnalyticsRun, 404);

        $data = $analytics->exportData($run);
        $filename = $data['filename'].'-'.$run->compiled_at->format('Y-m-d');

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
