<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\VisualizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $products = Product::query()
            ->with(['category', 'supplier', 'latestForecast'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('products.create', $this->formOptions());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::query()->create($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produit cree avec un stock initial de 0.');
    }

    public function show(Product $product, VisualizationService $visualizations): View
    {
        $product->load([
            'category',
            'supplier',
            'latestForecast',
            'alerts' => fn ($query) => $query->latest('triggered_at')->limit(5),
            'stockMovements' => fn ($query) => $query->with('user')->latest('movement_date')->limit(12),
        ]);

        $stockTrend = $visualizations->productStockTrend($product);

        return view('products.show', compact('product', 'stockTrend'));
    }

    public function edit(Product $product): View
    {
        abort_if($product->status !== RecordStatus::Active, 403);

        return view('products.edit', ['product' => $product] + $this->formOptions());
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produit mis a jour.');
    }

    public function archive(Product $product): RedirectResponse
    {
        $product->update(['status' => RecordStatus::Archived]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produit archive.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => Category::query()->where('status', RecordStatus::Active->value)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('status', RecordStatus::Active->value)->orderBy('name')->get(),
        ];
    }
}
