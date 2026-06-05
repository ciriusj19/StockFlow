<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Enums\InventoryStatus;
use App\Enums\MovementType;
use App\Enums\RecordStatus;
use App\Models\AnalyticsRun;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsCompilationService
{
    private const HISTORY_DAYS = 90;
    private const TARGET_DAYS = 60;

    public function currentRun(): ?AnalyticsRun
    {
        return AnalyticsRun::query()
            ->with('user')
            ->where('is_current', true)
            ->latest('compiled_at')
            ->first();
    }

    public function compile(User $user): AnalyticsRun
    {
        return DB::transaction(function () use ($user) {
            $periodEnd = now();
            $periodStart = $periodEnd->copy()->subDays(self::HISTORY_DAYS);

            AnalyticsRun::query()->update(['is_current' => false]);

            $run = AnalyticsRun::query()->create([
                'user_id' => $user->getKey(),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'status' => 'completed',
                'is_current' => true,
                'compiled_at' => $periodEnd,
            ]);

            $productKpis = $this->compileProductKpis($run, $periodStart, $periodEnd);
            $this->compileCategoryKpis($run, $productKpis);
            $this->compileSupplierKpis($run, $productKpis);
            $this->compileInventoryKpis($run);

            $run->update(['summary' => $this->summary($run, $productKpis)]);

            return $run->fresh(['user']);
        });
    }

    public function dashboardData(?AnalyticsRun $run): array
    {
        if (! $run) {
            return [
                'run' => null,
                'summary' => [],
                'priorityProducts' => collect(),
                'runHistory' => collect(),
                'charts' => $this->emptyCharts(),
            ];
        }

        $run->load(['user', 'productKpis', 'categoryKpis', 'supplierKpis', 'inventoryKpis']);
        $summary = $this->resolvedSummary($run);
        $run->setAttribute('summary', $summary);
        $runHistory = $this->runHistory();
        $runHistory->each(function (AnalyticsRun $history): void {
            $history->setAttribute('summary', $this->resolvedSummary($history));
        });

        return [
            'run' => $run,
            'summary' => $summary,
            'priorityProducts' => $run->productKpis
                ->sortByDesc(fn ($kpi): float => ((int) $kpi->risk_score * 100000) + ((int) $kpi->open_alerts_count * 1000) + (float) $kpi->recommended_quantity)
                ->take(10)
                ->values(),
            'runHistory' => $runHistory,
            'charts' => [
                'category_risk' => [
                    'labels' => $run->categoryKpis->pluck('category_name')->values()->all(),
                    'series' => $run->categoryKpis->pluck('average_risk_score')->map(fn ($value) => round((float) $value, 1))->values()->all(),
                ],
                'supplier_dependency' => [
                    'labels' => $run->supplierKpis->pluck('supplier_name')->values()->all(),
                    'series' => $run->supplierKpis->pluck('critical_products_count')->map(fn ($value) => (int) $value)->values()->all(),
                ],
                'inventory_reliability' => [
                    'labels' => $run->inventoryKpis->pluck('product_name')->values()->all(),
                    'series' => $run->inventoryKpis->pluck('reliability_score')->map(fn ($value) => round((float) $value, 1))->values()->all(),
                ],
                'data_quality' => [
                    'labels' => ['Qualité exploitable'],
                    'series' => [round((float) ($summary['data_quality_score'] ?? 0), 1)],
                ],
                'alert_trend' => $this->alertTrend(),
            ],
        ];
    }

    public function exportData(AnalyticsRun $run): array
    {
        $run->load(['user', 'productKpis', 'categoryKpis', 'supplierKpis', 'inventoryKpis']);
        $summary = $this->resolvedSummary($run);
        $rows = [
            ['section' => 'Compilation', 'item' => 'Période', 'indicator' => 'Fenêtre analysée', 'value' => $run->period_start->format('d/m/Y').' - '.$run->period_end->format('d/m/Y')],
            ['section' => 'Compilation', 'item' => 'Auteur', 'indicator' => 'Compilation', 'value' => $run->user?->name ?? '-'],
            ['section' => 'Compilation', 'item' => 'Statut', 'indicator' => 'Résultat', 'value' => $run->status],
            ['section' => 'KPI', 'item' => 'Stock', 'indicator' => 'Produits actifs', 'value' => (string) ($summary['products_count'] ?? 0)],
            ['section' => 'KPI', 'item' => 'Risque', 'indicator' => 'Produits sous seuil critique', 'value' => (string) ($summary['critical_stock_products_count'] ?? 0)],
            ['section' => 'KPI', 'item' => 'Alertes', 'indicator' => 'Alertes ouvertes', 'value' => (string) ($summary['open_alerts_count'] ?? 0)],
            ['section' => 'KPI', 'item' => 'Réapprovisionnement', 'indicator' => 'Quantité totale à commander', 'value' => number_format((float) ($summary['recommended_quantity_total'] ?? 0), 0, ',', ' ')],
            ['section' => 'Qualité', 'item' => 'Données', 'indicator' => 'Score qualité', 'value' => number_format((float) ($summary['data_quality_score'] ?? 0), 1, ',', ' ').' %'],
            ['section' => 'Qualité', 'item' => 'Prévisions', 'indicator' => 'Produits sans consommation', 'value' => (string) ($summary['products_without_consumption_count'] ?? 0)],
            ['section' => 'Qualité', 'item' => 'Inventaires', 'indicator' => 'Produits avec écarts', 'value' => (string) ($summary['inventory_sensitive_products_count'] ?? 0)],
        ];

        $run->productKpis
            ->sortByDesc(fn ($kpi): float => ((int) $kpi->risk_score * 100000) + ((int) $kpi->open_alerts_count * 1000) + (float) $kpi->recommended_quantity)
            ->take(12)
            ->each(function ($kpi) use (&$rows): void {
                $rows[] = [
                    'section' => 'Produits prioritaires',
                    'item' => $kpi->product_name,
                    'indicator' => $kpi->risk_label,
                    'value' => number_format((float) $kpi->recommended_quantity, 0, ',', ' ').' '.$kpi->unit.' à commander',
                ];
            });

        $run->categoryKpis
            ->sortByDesc('average_risk_score')
            ->take(6)
            ->each(function ($kpi) use (&$rows): void {
                $rows[] = [
                    'section' => 'Catégories à risque',
                    'item' => $kpi->category_name,
                    'indicator' => 'Risque moyen',
                    'value' => number_format((float) $kpi->average_risk_score, 1, ',', ' '),
                ];
            });

        $run->supplierKpis
            ->sortByDesc('critical_products_count')
            ->take(6)
            ->each(function ($kpi) use (&$rows): void {
                $rows[] = [
                    'section' => 'Fournisseurs sensibles',
                    'item' => $kpi->supplier_name,
                    'indicator' => 'Produits critiques liés',
                    'value' => (string) $kpi->critical_products_count,
                ];
            });

        $run->inventoryKpis
            ->sortBy('reliability_score')
            ->take(6)
            ->each(function ($kpi) use (&$rows): void {
                $rows[] = [
                    'section' => 'Fiabilité inventaire',
                    'item' => $kpi->product_name,
                    'indicator' => 'Fiabilité',
                    'value' => number_format((float) $kpi->reliability_score, 1, ',', ' ').' %',
                ];
            });

        return [
            'title' => 'Synthèse décisionnelle StockFlow',
            'filename' => 'analytics-summary',
            'description' => 'Synthèse consolidée pour réunion : période, KPI, qualité des données et priorités.',
            'columns' => ['section' => 'Section', 'item' => 'Élément', 'indicator' => 'Indicateur', 'value' => 'Valeur'],
            'rows' => $rows,
        ];
    }

    private function compileProductKpis(AnalyticsRun $run, $periodStart, $periodEnd): Collection
    {
        return Product::query()
            ->with(['category', 'supplier', 'alerts'])
            ->where('status', RecordStatus::Active->value)
            ->get()
            ->map(function (Product $product) use ($run, $periodStart, $periodEnd) {
                $totalExits = (float) $product->stockMovements()
                    ->where('type', MovementType::Exit->value)
                    ->whereBetween('movement_date', [$periodStart, $periodEnd])
                    ->sum('quantity');

                $averageDailyUsage = $totalExits / self::HISTORY_DAYS;
                $stock = (float) $product->current_stock;
                $remainingDays = $averageDailyUsage > 0 ? $stock / $averageDailyUsage : null;
                $riskScore = $remainingDays === null ? 25 : $this->riskScore($remainingDays);
                $recommendedQuantity = max(0, ($averageDailyUsage * self::TARGET_DAYS) - $stock);

                return $run->productKpis()->create([
                    'product_id' => $product->getKey(),
                    'category_id' => $product->category_id,
                    'supplier_id' => $product->supplier_id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'category_name' => $product->category?->name,
                    'supplier_name' => $product->supplier?->name,
                    'unit' => $product->unit,
                    'stock' => $stock,
                    'critical_stock' => (float) $product->critical_stock,
                    'stock_value' => $stock * (float) $product->purchase_price,
                    'average_daily_usage' => $averageDailyUsage,
                    'remaining_days' => $remainingDays,
                    'predicted_out_date' => $remainingDays === null ? null : $periodEnd->copy()->addDays((int) ceil($remainingDays))->toDateString(),
                    'risk_score' => $riskScore,
                    'risk_label' => $this->riskLabel($riskScore),
                    'recommended_quantity' => $recommendedQuantity,
                    'open_alerts_count' => $product->alerts->whereIn('status', [AlertStatus::New, AlertStatus::Viewed])->count(),
                    'resolved_alerts_count' => $product->alerts->where('status', AlertStatus::Resolved)->count(),
                    'latest_alert_at' => $product->alerts->max('triggered_at'),
                ]);
            });
    }

    private function compileCategoryKpis(AnalyticsRun $run, Collection $productKpis): void
    {
        $productKpis->groupBy('category_id')->each(function (Collection $group) use ($run) {
            $first = $group->first();

            $run->categoryKpis()->create([
                'category_id' => $first->category_id,
                'category_name' => $first->category_name ?? 'Sans categorie',
                'products_count' => $group->count(),
                'critical_products_count' => $group->where('risk_score', '>=', 75)->count(),
                'open_alerts_count' => $group->sum('open_alerts_count'),
                'average_risk_score' => $group->avg('risk_score') ?? 0,
                'stock_value' => $group->sum('stock_value'),
                'recommended_quantity_total' => $group->sum('recommended_quantity'),
            ]);
        });
    }

    private function compileSupplierKpis(AnalyticsRun $run, Collection $productKpis): void
    {
        $productKpis->groupBy('supplier_id')->each(function (Collection $group) use ($run) {
            $first = $group->first();

            $run->supplierKpis()->create([
                'supplier_id' => $first->supplier_id,
                'supplier_name' => $first->supplier_name ?? 'Sans fournisseur',
                'products_count' => $group->count(),
                'critical_products_count' => $group->where('risk_score', '>=', 75)->count(),
                'open_alerts_count' => $group->sum('open_alerts_count'),
                'average_risk_score' => $group->avg('risk_score') ?? 0,
                'stock_value' => $group->sum('stock_value'),
                'recommended_quantity_total' => $group->sum('recommended_quantity'),
            ]);
        });
    }

    private function compileInventoryKpis(AnalyticsRun $run): void
    {
        InventoryItem::query()
            ->with(['product', 'inventory'])
            ->whereHas('inventory', fn ($query) => $query->where('status', InventoryStatus::Validated->value))
            ->get()
            ->groupBy('product_id')
            ->each(function (Collection $items) use ($run) {
                $product = $items->first()->product;
                $expected = (float) $items->sum('expected_quantity');
                $absoluteDifference = (float) $items->sum(fn ($item) => abs((float) $item->difference));
                $reliability = $expected > 0 ? max(0, 100 - (($absoluteDifference / $expected) * 100)) : 100;

                $run->inventoryKpis()->create([
                    'product_id' => $product?->getKey(),
                    'product_name' => $product?->name ?? 'Produit supprime',
                    'sku' => $product?->sku ?? '-',
                    'unit' => $product?->unit ?? 'unite',
                    'inventories_count' => $items->count(),
                    'total_expected_quantity' => $expected,
                    'total_difference' => $items->sum('difference'),
                    'absolute_difference' => $absoluteDifference,
                    'positive_difference' => $items->filter(fn ($item) => (float) $item->difference > 0)->sum('difference'),
                    'negative_difference' => abs($items->filter(fn ($item) => (float) $item->difference < 0)->sum('difference')),
                    'reliability_score' => $reliability,
                    'last_inventory_date' => $items->max(fn ($item) => $item->inventory?->inventory_date),
                ]);
            });
    }

    private function summary(AnalyticsRun $run, Collection $productKpis): array
    {
        $productsCount = max($productKpis->count(), 1);
        $withoutConsumption = $productKpis
            ->filter(fn ($kpi): bool => (float) $kpi->average_daily_usage <= 0.0)
            ->count();
        $nonEstimable = $productKpis
            ->filter(fn ($kpi): bool => $kpi->remaining_days === null)
            ->count();
        $inventorySensitive = $run->inventoryKpis()
            ->where('absolute_difference', '>', 0)
            ->count();
        $qualityScore = max(0, round(
            100
            - (($withoutConsumption / $productsCount) * 35)
            - (($nonEstimable / $productsCount) * 25)
            - (($inventorySensitive / $productsCount) * 20),
            1,
        ));

        return [
            'products_count' => $productKpis->count(),
            'critical_products_count' => $productKpis->filter(fn ($kpi) => (float) $kpi->stock <= (float) $kpi->critical_stock)->count(),
            'critical_stock_products_count' => $productKpis->filter(fn ($kpi) => (float) $kpi->stock <= (float) $kpi->critical_stock)->count(),
            'open_alerts_count' => $productKpis->sum('open_alerts_count'),
            'resolved_alerts_count' => $productKpis->sum('resolved_alerts_count'),
            'high_risk_products_count' => $productKpis->where('risk_score', '>=', 75)->count(),
            'recommended_quantity_total' => $productKpis->sum('recommended_quantity'),
            'stock_value_total' => $productKpis->sum('stock_value'),
            'non_estimable_forecasts_count' => $nonEstimable,
            'products_without_consumption_count' => $withoutConsumption,
            'inventory_sensitive_products_count' => $inventorySensitive,
            'data_quality_score' => $qualityScore,
        ];
    }

    private function alertTrend(): array
    {
        $runs = AnalyticsRun::query()
            ->latest('compiled_at')
            ->take(8)
            ->get()
            ->sortBy('compiled_at')
            ->values();
        $runs->each(function (AnalyticsRun $run): void {
            $run->setAttribute('summary', $this->resolvedSummary($run));
        });

        return [
            'labels' => $runs->map(fn ($run) => $run->compiled_at->format('d/m'))->values()->all(),
            'series' => [
                ['name' => 'Alertes ouvertes', 'data' => $runs->map(fn ($run) => (int) ($run->summary['open_alerts_count'] ?? 0))->values()->all()],
                ['name' => 'Risques élevés', 'data' => $runs->map(fn ($run) => (int) ($run->summary['high_risk_products_count'] ?? 0))->values()->all()],
            ],
        ];
    }

    private function emptyCharts(): array
    {
        return [
            'category_risk' => ['labels' => [], 'series' => []],
            'supplier_dependency' => ['labels' => [], 'series' => []],
            'inventory_reliability' => ['labels' => [], 'series' => []],
            'data_quality' => ['labels' => [], 'series' => []],
            'alert_trend' => ['labels' => [], 'series' => []],
        ];
    }

    private function runHistory(): Collection
    {
        return AnalyticsRun::query()
            ->with('user')
            ->latest('compiled_at')
            ->limit(5)
            ->get();
    }

    private function resolvedSummary(AnalyticsRun $run): array
    {
        $summary = $run->summary ?? [];

        if (array_key_exists('data_quality_score', $summary)) {
            return $summary;
        }

        if (! $run->relationLoaded('productKpis')) {
            $run->load('productKpis');
        }

        return array_merge($summary, $this->summary($run, $run->productKpis));
    }

    private function riskScore(float $remainingDays): int
    {
        return match (true) {
            $remainingDays <= 7 => 100,
            $remainingDays <= 15 => 75,
            $remainingDays <= 30 => 50,
            default => 25,
        };
    }

    private function riskLabel(int $riskScore): string
    {
        return match (true) {
            $riskScore >= 100 => 'Critique',
            $riskScore >= 75 => 'Élevé',
            $riskScore >= 50 => 'Modéré',
            default => 'Faible',
        };
    }
}
