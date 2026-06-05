<?php

namespace App\Http\Controllers;

use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Services\AlertService;
use App\Services\VisualizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request, VisualizationService $visualizations): View
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(AlertStatus::class)],
        ]);

        $status = $validated['status'] ?? null;

        $newAlerts = Alert::query()
            ->with('product')
            ->where('status', AlertStatus::New->value)
            ->latest('triggered_at')
            ->paginate(6, ['*'], 'new_page')
            ->withQueryString();

        $viewedAlerts = Alert::query()
            ->with('product')
            ->where('status', AlertStatus::Viewed->value)
            ->latest('triggered_at')
            ->paginate(6, ['*'], 'viewed_page')
            ->withQueryString();

        $resolvedAlerts = Alert::query()
            ->with('product')
            ->where('status', AlertStatus::Resolved->value)
            ->latest('resolved_at')
            ->latest('triggered_at')
            ->paginate(6, ['*'], 'resolved_page')
            ->withQueryString();

        $openStatuses = [AlertStatus::New->value, AlertStatus::Viewed->value];
        $alertSummary = [
            'open_alerts' => Alert::query()->whereIn('status', $openStatuses)->count(),
            'open_products' => Alert::query()->whereIn('status', $openStatuses)->distinct('product_id')->count('product_id'),
            'resolved_recently' => Alert::query()
                ->where('status', AlertStatus::Resolved->value)
                ->where('resolved_at', '>=', now()->subDays(30))
                ->count(),
        ];

        $alertBands = $visualizations->alertStockBands();

        return view('alerts.index', compact('alertBands', 'alertSummary', 'newAlerts', 'viewedAlerts', 'resolvedAlerts', 'status'));
    }

    public function show(Alert $alert, AlertService $alertService): View
    {
        $alert = $alertService->markAsViewed($alert);
        $alert->load('product.latestForecast');

        return view('alerts.show', compact('alert'));
    }

    public function resolve(Alert $alert, AlertService $alertService): RedirectResponse
    {
        $alertService->resolve($alert);

        return redirect()
            ->route('alerts.show', $alert)
            ->with('success', 'Alerte résolue et conservée dans l\'historique.');
    }
}
