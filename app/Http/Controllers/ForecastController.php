<?php

namespace App\Http\Controllers;

use App\Models\Forecast;
use App\Services\ForecastService;
use App\Services\VisualizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ForecastController extends Controller
{
    public function index(VisualizationService $visualizations): View
    {
        $latestForecastIds = Forecast::query()
            ->selectRaw('MAX(id)')
            ->groupBy('product_id');

        $forecasts = Forecast::query()
            ->with('product')
            ->whereIn('id', $latestForecastIds)
            ->orderByDesc('risk_score')
            ->orderBy('predicted_out_date')
            ->paginate(15);

        $charts = $visualizations->forecastCharts();

        return view('forecasts.index', compact('forecasts', 'charts'));
    }

    public function refresh(ForecastService $forecastService): RedirectResponse
    {
        $forecastService->generateAll();

        return redirect()
            ->route('forecasts.index')
            ->with('success', 'Prévisions recalculées à partir des mouvements historiques.');
    }

    public function show(Forecast $forecast): View
    {
        $forecast->load('product');
        $history = $forecast->product
            ->forecasts()
            ->latest('generated_at')
            ->limit(10)
            ->get();

        return view('forecasts.show', compact('forecast', 'history'));
    }
}
