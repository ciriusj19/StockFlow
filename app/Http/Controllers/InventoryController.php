<?php

namespace App\Http\Controllers;

use App\Enums\InventoryStatus;
use App\Enums\RecordStatus;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\VisualizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(VisualizationService $visualizations): View
    {
        $inventories = Inventory::query()
            ->with('user')
            ->withCount('items')
            ->latest('inventory_date')
            ->latest('id')
            ->paginate(15);

        $differenceChart = $visualizations->inventoryDifferences();

        return view('inventories.index', compact('inventories', 'differenceChart'));
    }

    public function create(Request $request): View
    {
        $selectedProductIds = [];
        $requestedProductId = $request->integer('product_id');

        if ($requestedProductId > 0 && Product::query()
            ->where('id', $requestedProductId)
            ->where('status', RecordStatus::Active->value)
            ->exists()) {
            $selectedProductIds[] = $requestedProductId;
        }

        $products = Product::query()
            ->where('status', RecordStatus::Active->value)
            ->orderBy('name')
            ->get();

        return view('inventories.create', compact('products', 'selectedProductIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where('status', RecordStatus::Active->value),
            ],
        ]);

        $inventory = DB::transaction(function () use ($request, $validated) {
            $inventory = Inventory::query()->create([
                'user_id' => $request->user()->getKey(),
                'inventory_date' => $validated['inventory_date'],
                'status' => InventoryStatus::Draft,
                'notes' => $validated['notes'] ?? null,
            ]);

            Product::query()
                ->whereIn('id', $validated['product_ids'])
                ->each(function (Product $product) use ($inventory) {
                    $inventory->items()->create([
                        'product_id' => $product->getKey(),
                        'expected_quantity' => $product->current_stock,
                        'actual_quantity' => $product->current_stock,
                        'difference' => 0,
                    ]);
                });

            return $inventory;
        });

        return redirect()
            ->route('inventories.show', $inventory)
            ->with('success', 'Inventaire cree. Saisissez les quantites observees avant validation.');
    }

    public function show(Inventory $inventory): View
    {
        $inventory->load(['user', 'items.product']);

        return view('inventories.show', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        abort_if($inventory->status === InventoryStatus::Validated, 403);

        $validated = $request->validate([
            'actual_quantities' => ['required', 'array'],
            'actual_quantities.*' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($inventory, $validated) {
            $inventory->load('items');

            foreach ($inventory->items as $item) {
                abort_unless(array_key_exists($item->getKey(), $validated['actual_quantities']), 422);

                $actualQuantity = (float) $validated['actual_quantities'][$item->getKey()];

                $item->update([
                    'actual_quantity' => $actualQuantity,
                    'difference' => $actualQuantity - (float) $item->expected_quantity,
                ]);
            }

            $inventory->update(['notes' => $validated['notes'] ?? null]);
        });

        return redirect()
            ->route('inventories.show', $inventory)
            ->with('success', 'Comptage enregistre.');
    }

    public function validateInventory(Inventory $inventory, InventoryService $inventoryService, Request $request): RedirectResponse
    {
        $inventoryService->validate($inventory, $request->user());

        return redirect()
            ->route('inventories.show', $inventory)
            ->with('success', 'Inventaire valide. Les ajustements ont ete historises.');
    }
}
