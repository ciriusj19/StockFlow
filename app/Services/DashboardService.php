<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Enums\MovementType;
use App\Enums\RecordStatus;
use App\Models\Alert;
use App\Models\Forecast;
use App\Models\Product;
use App\Models\StockMovement;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $activeProducts = Product::query()->where('status', RecordStatus::Active->value);
        $openStatuses = [AlertStatus::New->value, AlertStatus::Viewed->value];

        return [
            'metrics' => [
                'products' => (clone $activeProducts)->count(),
                'critical_products' => (clone $activeProducts)
                    ->whereColumn('current_stock', '<=', 'critical_stock')
                    ->count(),
                'out_of_stock_products' => (clone $activeProducts)
                    ->where('current_stock', '<=', 0)
                    ->count(),
                'open_alerts' => Alert::query()->whereIn('status', $openStatuses)->count(),
                'high_risk_forecasts' => Forecast::query()
                    ->whereIn('id', $this->latestForecastIds())
                    ->where('risk_score', '>=', 75)
                    ->count(),
            ],
            'alerts' => Alert::query()
                ->with('product')
                ->whereIn('status', $openStatuses)
                ->latest('triggered_at')
                ->limit(5)
                ->get(),
            'forecasts' => Forecast::query()
                ->with('product')
                ->whereIn('id', $this->latestForecastIds())
                ->orderByDesc('risk_score')
                ->orderBy('predicted_out_date')
                ->limit(6)
                ->get(),
            'movements' => StockMovement::query()
                ->with(['product', 'user'])
                ->latest('movement_date')
                ->limit(6)
                ->get(),
            'charts' => [
                'risk_distribution' => $this->riskDistribution(),
                'movement_activity' => $this->movementActivity(),
                'top_risks' => $this->topRisks(),
            ],
        ];
    }

    private function latestForecastIds()
    {
        return Forecast::query()
            ->selectRaw('MAX(id)')
            ->groupBy('product_id');
    }

    /**
     * @return array{labels: list<string>, series: list<int>}
     */
    private function riskDistribution(): array
    {
        $counts = Forecast::query()
            ->whereIn('id', $this->latestForecastIds())
            ->selectRaw('risk_score, COUNT(*) as total')
            ->groupBy('risk_score')
            ->pluck('total', 'risk_score');

        return [
            'labels' => ['Faible', 'Modéré', 'Élevé', 'Critique'],
            'series' => [
                (int) $counts->get(25, 0),
                (int) $counts->get(50, 0),
                (int) $counts->get(75, 0),
                (int) $counts->get(100, 0),
            ],
        ];
    }

    /**
     * @return array{labels: list<string>, entries: list<float>, exits: list<float>}
     */
    private function movementActivity(): array
    {
        $days = collect(range(29, 0))
            ->map(fn (int $daysAgo) => today()->subDays($daysAgo));

        $totals = StockMovement::query()
            ->where('movement_date', '>=', $days->first()->copy()->startOfDay())
            ->selectRaw('DATE(movement_date) as movement_day, type, SUM(quantity) as total')
            ->groupBy('movement_day', 'type')
            ->get()
            ->mapWithKeys(fn (StockMovement $movement): array => [
                $movement->movement_day.'|'.$movement->getRawOriginal('type') => (float) $movement->total,
            ]);

        return [
            'labels' => $days->map(fn ($day): string => $day->translatedFormat('d M'))->all(),
            'entries' => $days->map(fn ($day): float => (float) $totals->get(
                $day->toDateString().'|'.MovementType::Entry->value,
                0,
            ))->all(),
            'exits' => $days->map(fn ($day): float => (float) $totals->get(
                $day->toDateString().'|'.MovementType::Exit->value,
                0,
            ))->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, series: list<int>}
     */
    private function topRisks(): array
    {
        $forecasts = Forecast::query()
            ->with('product')
            ->whereIn('id', $this->latestForecastIds())
            ->orderByDesc('risk_score')
            ->orderBy('predicted_out_date')
            ->limit(5)
            ->get();

        return [
            'labels' => $forecasts->map(fn (Forecast $forecast): string => $forecast->product->name)->all(),
            'series' => $forecasts->map(fn (Forecast $forecast): int => $forecast->risk_score)->all(),
        ];
    }
}
