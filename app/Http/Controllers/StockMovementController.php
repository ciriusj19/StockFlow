<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;

class StockMovementController extends Controller
{
    public function store(StoreStockMovementRequest $request, Product $product, StockService $stockService): RedirectResponse
    {
        $type = MovementType::from($request->validated('type'));
        $permission = match ($type) {
            MovementType::Entry => 'stock.entry',
            MovementType::Exit => 'stock.exit',
            MovementType::Adjustment => 'stock.adjustment',
        };

        abort_unless($request->user()->can($permission), 403);

        $quantity = (float) $request->validated('quantity');

        $movement = match ($type) {
            MovementType::Entry => $stockService->enter(
                $product,
                $request->user(),
                $quantity,
                $request->validated('reference'),
                $request->validated('notes'),
            ),
            MovementType::Exit => $stockService->exit(
                $product,
                $request->user(),
                $quantity,
                $request->validated('reference'),
                $request->validated('notes'),
            ),
            MovementType::Adjustment => $stockService->adjustTo(
                $product,
                $request->user(),
                $quantity,
                $request->validated('reference'),
                $request->validated('notes'),
            ),
        };

        return redirect()
            ->route('products.show', $product)
            ->with('success', $movement ? 'Mouvement enregistre.' : 'Aucun ajustement necessaire.');
    }
}
