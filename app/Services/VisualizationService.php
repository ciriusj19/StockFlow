<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Enums\InventoryStatus;
use App\Enums\MovementType;
use App\Models\Alert;
use App\Models\Forecast;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class VisualizationService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function alertStockBands(): array
    {
        $pendingStatuses = [AlertStatus::New->value, AlertStatus::Viewed->value];

        return Alert::query()
            ->with(['product.latestForecast'])
            ->whereIn('status', $pendingStatuses)
            ->latest('triggered_at')
            ->get()
            ->unique('product_id')
            ->map(function (Alert $alert): array {
                $product = $alert->product;
                $forecast = $product->latestForecast;
                $criticalStock = (float) $product->critical_stock;
                $currentStock = (float) $product->current_stock;
                $ratio = $criticalStock > 0 ? $currentStock / $criticalStock : ($currentStock > 0 ? 1.5 : 0.0);
                $boundedRatio = min(max($ratio, 0), 1.5);

                return [
                    'alertId' => $alert->getKey(),
                    'product' => $product->name,
                    'sku' => $product->sku,
                    'risk' => $forecast?->riskLabel(),
                    'riskTone' => $forecast?->riskBadgeTone(),
                    'barColor' => $forecast ? $this->riskColor($forecast->risk_score) : $this->stockBarColor($ratio),
                    'height' => max(4, round(($boundedRatio / 1.5) * 100, 2)),
                    'width' => round(($boundedRatio / 1.5) * 100, 2),
                    'threshold' => round((1 / 1.5) * 100, 2),
                    'sort' => [
                        -($forecast?->risk_score ?? 0),
                        $ratio,
                        $product->name,
                    ],
                ];
            })
            ->sortBy('sort')
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<string, list<mixed>>>
     */
    public function forecastCharts(): array
    {
        $forecasts = Forecast::query()
            ->with('product')
            ->whereIn('id', $this->latestForecastIds())
            ->orderByDesc('risk_score')
            ->orderBy('predicted_out_date')
            ->get();

        $days = $forecasts
            ->filter(fn (Forecast $forecast): bool => $forecast->predicted_out_date !== null)
            ->sortBy('predicted_out_date')
            ->take(8)
            ->values();

        $recommended = $forecasts
            ->filter(fn (Forecast $forecast): bool => (float) $forecast->recommended_quantity > 0)
            ->sortByDesc(fn (Forecast $forecast): float => (float) $forecast->recommended_quantity)
            ->take(8)
            ->values();

        $priority = $forecasts
            ->filter(fn (Forecast $forecast): bool => (float) $forecast->recommended_quantity > 0)
            ->sortByDesc(fn (Forecast $forecast): float => ((int) $forecast->risk_score * 100000) + (float) $forecast->recommended_quantity)
            ->take(8)
            ->values();

        $consumption = StockMovement::query()
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->where('stock_movements.type', MovementType::Exit->value)
            ->where('stock_movements.movement_date', '>=', now()->subDays(90))
            ->select('products.name')
            ->selectRaw('SUM(stock_movements.quantity) as total')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'days_remaining' => [
                'labels' => $days->map(fn (Forecast $forecast): string => $forecast->product->name)->all(),
                'series' => $days->map(fn (Forecast $forecast): int => max(0, (int) today()->diffInDays($forecast->predicted_out_date, false)))->all(),
                'colors' => $days->map(fn (Forecast $forecast): string => $this->riskColor($forecast->risk_score))->all(),
            ],
            'recommended_quantities' => [
                'labels' => $recommended->map(fn (Forecast $forecast): string => $forecast->product->name)->all(),
                'series' => $recommended->map(fn (Forecast $forecast): float => (float) $forecast->recommended_quantity)->all(),
            ],
            'priority_restock' => [
                'labels' => $priority->map(fn (Forecast $forecast): string => $forecast->product->name)->all(),
                'series' => $priority->map(fn (Forecast $forecast): float => (float) $forecast->recommended_quantity)->all(),
                'colors' => $priority->map(fn (Forecast $forecast): string => $this->riskColor($forecast->risk_score))->all(),
            ],
            'top_consumption' => [
                'labels' => $consumption->pluck('name')->all(),
                'series' => $consumption->map(fn ($row): float => (float) $row->total)->all(),
            ],
        ];
    }

    /**
     * @return array{labels: list<string>, stock: list<float>, threshold: list<float>}
     */
    public function productStockTrend(Product $product): array
    {
        $movements = StockMovement::query()
            ->where('product_id', $product->getKey())
            ->where('movement_date', '>=', now()->subDays(90))
            ->orderBy('movement_date')
            ->get();

        if ($movements->isEmpty()) {
            return [
                'labels' => ['Aujourd\'hui'],
                'stock' => [(float) $product->current_stock],
                'threshold' => [(float) $product->critical_stock],
            ];
        }

        return [
            'labels' => $movements->map(fn (StockMovement $movement): string => $movement->movement_date->translatedFormat('d M'))->all(),
            'stock' => $movements->map(fn (StockMovement $movement): float => (float) $movement->stock_after)->all(),
            'threshold' => $movements->map(fn (): float => (float) $product->critical_stock)->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, series: list<float>, colors: list<string>}
     */
    public function inventoryDifferences(): array
    {
        $rows = DB::table('inventory_items')
            ->join('inventories', 'inventories.id', '=', 'inventory_items.inventory_id')
            ->join('products', 'products.id', '=', 'inventory_items.product_id')
            ->where('inventories.status', InventoryStatus::Validated->value)
            ->select('products.name')
            ->selectRaw('SUM(inventory_items.difference) as total_difference')
            ->groupBy('products.id', 'products.name')
            ->havingRaw('SUM(inventory_items.difference) != 0')
            ->orderByRaw('ABS(SUM(inventory_items.difference)) DESC')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->pluck('name')->all(),
            'series' => $rows->map(fn ($row): float => (float) $row->total_difference)->all(),
            'colors' => $rows->map(fn ($row): string => ((float) $row->total_difference) >= 0 ? '#059669' : '#e11d48')->all(),
        ];
    }

    private function latestForecastIds()
    {
        return Forecast::query()
            ->selectRaw('MAX(id)')
            ->groupBy('product_id');
    }

    private function stockBarColor(float $ratio): string
    {
        return match (true) {
            $ratio <= 0 => '#be123c',
            $ratio <= 0.5 => '#f43f5e',
            $ratio <= 1 => '#f97316',
            default => '#059669',
        };
    }

    private function riskColor(int $riskScore): string
    {
        return match (true) {
            $riskScore >= 100 => '#e11d48',
            $riskScore >= 75 => '#ea580c',
            $riskScore >= 50 => '#d97706',
            default => '#059669',
        };
    }
}
