<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\Forecast;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;

class ReportService
{
    /**
     * @return list<array{slug: string, title: string, description: string, count: int}>
     */
    public function summaries(): array
    {
        return array_map(function (string $slug): array {
            $report = $this->make($slug);

            return [
                'slug' => $slug,
                'title' => $report['title'],
                'description' => $report['description'],
                'count' => count($report['rows']),
            ];
        }, $this->slugs());
    }

    /**
     * @return array{title: string, description: string, filename: string, columns: array<string, string>, rows: list<array<string, string>>}
     */
    public function make(string $report): array
    {
        return match ($report) {
            'stock' => $this->stock(),
            'critical-products' => $this->criticalProducts(),
            'movements' => $this->movements(),
            'inventories' => $this->inventories(),
            'forecasts' => $this->forecasts(),
            default => abort(404),
        };
    }

    /**
     * @return list<string>
     */
    public function slugs(): array
    {
        return [
            'stock',
            'critical-products',
            'movements',
            'inventories',
            'forecasts',
        ];
    }

    /**
     * @return array{title: string, description: string, filename: string, columns: array<string, string>, rows: list<array<string, string>>}
     */
    private function stock(): array
    {
        $products = Product::query()
            ->with(['category', 'supplier'])
            ->where('status', RecordStatus::Active->value)
            ->orderBy('name')
            ->get();

        return [
            'title' => 'Etat du stock',
            'description' => 'Catalogue actif avec stock disponible et seuil critique.',
            'filename' => 'etat-du-stock',
            'columns' => [
                'product' => 'Produit',
                'sku' => 'SKU',
                'category' => 'Categorie',
                'supplier' => 'Fournisseur',
                'stock' => 'Stock actuel',
                'critical_stock' => 'Seuil critique',
            ],
            'rows' => $products->map(fn (Product $product): array => [
                'product' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name,
                'supplier' => $product->supplier->name,
                'stock' => $this->quantity($product->current_stock, $product->unit),
                'critical_stock' => $this->quantity($product->critical_stock, $product->unit),
            ])->all(),
        ];
    }

    /**
     * @return array{title: string, description: string, filename: string, columns: array<string, string>, rows: list<array<string, string>>}
     */
    private function criticalProducts(): array
    {
        $products = Product::query()
            ->with(['category', 'supplier'])
            ->where('status', RecordStatus::Active->value)
            ->whereColumn('current_stock', '<=', 'critical_stock')
            ->orderBy('current_stock')
            ->orderBy('name')
            ->get();

        return [
            'title' => 'Produits critiques',
            'description' => 'Produits dont le stock est inferieur ou egal au seuil critique.',
            'filename' => 'produits-critiques',
            'columns' => [
                'product' => 'Produit',
                'sku' => 'SKU',
                'category' => 'Categorie',
                'supplier' => 'Fournisseur',
                'stock' => 'Stock actuel',
                'critical_stock' => 'Seuil critique',
            ],
            'rows' => $products->map(fn (Product $product): array => [
                'product' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name,
                'supplier' => $product->supplier->name,
                'stock' => $this->quantity($product->current_stock, $product->unit),
                'critical_stock' => $this->quantity($product->critical_stock, $product->unit),
            ])->all(),
        ];
    }

    /**
     * @return array{title: string, description: string, filename: string, columns: array<string, string>, rows: list<array<string, string>>}
     */
    private function movements(): array
    {
        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->latest('movement_date')
            ->get();

        return [
            'title' => 'Historique des mouvements',
            'description' => 'Journal immutable des entrées, sorties et ajustements.',
            'filename' => 'historique-des-mouvements',
            'columns' => [
                'date' => 'Date',
                'product' => 'Produit',
                'sku' => 'SKU',
                'type' => 'Type',
                'quantity' => 'Quantité',
                'stock_before' => 'Avant',
                'stock_after' => 'Apres',
                'reference' => 'Reference',
                'user' => 'Responsable',
            ],
            'rows' => $movements->map(fn (StockMovement $movement): array => [
                'date' => $movement->movement_date->format('d/m/Y H:i'),
                'product' => $movement->product->name,
                'sku' => $movement->product->sku,
                'type' => $movement->type->label(),
                'quantity' => $this->quantity($movement->quantity, $movement->product->unit),
                'stock_before' => $this->quantity($movement->stock_before, $movement->product->unit),
                'stock_after' => $this->quantity($movement->stock_after, $movement->product->unit),
                'reference' => $movement->reference ?? '-',
                'user' => $movement->user->name,
            ])->all(),
        ];
    }

    /**
     * @return array{title: string, description: string, filename: string, columns: array<string, string>, rows: list<array<string, string>>}
     */
    private function inventories(): array
    {
        $inventories = Inventory::query()
            ->with('user')
            ->withCount('items')
            ->withSum('items', 'difference')
            ->latest('inventory_date')
            ->get();

        return [
            'title' => 'Resultats des inventaires',
            'description' => 'Inventaires physiques et ecarts constates.',
            'filename' => 'resultats-des-inventaires',
            'columns' => [
                'date' => 'Date',
                'user' => 'Responsable',
                'status' => 'Statut',
                'products' => 'Produits',
                'difference' => 'Ecart total',
                'validated_at' => 'Validation',
            ],
            'rows' => $inventories->map(fn (Inventory $inventory): array => [
                'date' => $inventory->inventory_date->format('d/m/Y'),
                'user' => $inventory->user->name,
                'status' => $inventory->status->label(),
                'products' => (string) $inventory->items_count,
                'difference' => number_format((float) ($inventory->items_sum_difference ?? 0), 2, ',', ' '),
                'validated_at' => $inventory->validated_at?->format('d/m/Y H:i') ?? '-',
            ])->all(),
        ];
    }

    /**
     * @return array{title: string, description: string, filename: string, columns: array<string, string>, rows: list<array<string, string>>}
     */
    private function forecasts(): array
    {
        $latestForecastIds = Forecast::query()
            ->selectRaw('MAX(id)')
            ->groupBy('product_id');

        $forecasts = Forecast::query()
            ->with('product')
            ->whereIn('id', $latestForecastIds)
            ->orderByDesc('risk_score')
            ->orderBy('predicted_out_date')
            ->get();

        return [
            'title' => 'Prévisions de rupture',
            'description' => 'Derniere prevision disponible pour chaque produit.',
            'filename' => 'previsions-de-rupture',
            'columns' => [
                'product' => 'Produit',
                'sku' => 'SKU',
                'stock' => 'Stock actuel',
                'average_daily_usage' => 'CMJ',
                'predicted_out_date' => 'Rupture estimée',
                'risk_score' => 'Risque',
                'recommended_quantity' => 'À commander',
                'generated_at' => 'Generation',
            ],
            'rows' => $forecasts->map(fn (Forecast $forecast): array => [
                'product' => $forecast->product->name,
                'sku' => $forecast->product->sku,
                'stock' => $this->quantity($forecast->product->current_stock, $forecast->product->unit),
                'average_daily_usage' => number_format((float) $forecast->average_daily_usage, 2, ',', ' '),
                'predicted_out_date' => $forecast->predicted_out_date?->format('d/m/Y') ?? 'Non estimable',
                'risk_score' => $forecast->riskLabel(),
                'recommended_quantity' => $this->quantity($forecast->recommended_quantity, $forecast->product->unit),
                'generated_at' => $forecast->generated_at->format('d/m/Y H:i'),
            ])->all(),
        ];
    }

    private function quantity(float|string $quantity, string $unit): string
    {
        return number_format((float) $quantity, 2, ',', ' ').' '.$unit;
    }
}
