<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboardService): RedirectResponse|View
    {
        if (! $request->user()->can('forecasts.view')) {
            return redirect()->route(
                $request->user()->can('analytics.view')
                    ? 'analytics.index'
                    : ($request->user()->can('products.view') ? 'products.index' : 'profile'),
            );
        }

        return view('dashboard', $dashboardService->summary());
    }
}
