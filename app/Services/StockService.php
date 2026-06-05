<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\RecordStatus;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function __construct(private readonly AlertService $alertService)
    {
    }

    public function enter(Product $product, User $user, float $quantity, ?string $reference = null, ?string $notes = null): StockMovement
    {
        return $this->change($product, $user, MovementType::Entry, $quantity, $reference, $notes);
    }

    public function exit(Product $product, User $user, float $quantity, ?string $reference = null, ?string $notes = null): StockMovement
    {
        return $this->change($product, $user, MovementType::Exit, $quantity, $reference, $notes);
    }

    public function adjustTo(Product $product, User $user, float $targetStock, ?string $reference = null, ?string $notes = null): ?StockMovement
    {
        if ($targetStock < 0) {
            throw ValidationException::withMessages(['actual_quantity' => 'Le stock reel ne peut pas etre negatif.']);
        }

        return DB::transaction(function () use ($product, $user, $targetStock, $reference, $notes) {
            $lockedProduct = $this->lockActiveProduct($product);
            $stockBefore = (float) $lockedProduct->current_stock;

            if ($stockBefore === $targetStock) {
                return null;
            }

            return $this->createMovement(
                $lockedProduct,
                $user,
                MovementType::Adjustment,
                abs($targetStock - $stockBefore),
                $targetStock,
                $reference,
                $notes,
            );
        });
    }

    private function change(Product $product, User $user, MovementType $type, float $quantity, ?string $reference, ?string $notes): StockMovement
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'La quantite doit etre superieure a zero.']);
        }

        return DB::transaction(function () use ($product, $user, $type, $quantity, $reference, $notes) {
            $lockedProduct = $this->lockActiveProduct($product);
            $stockBefore = (float) $lockedProduct->current_stock;
            $stockAfter = $type === MovementType::Entry
                ? $stockBefore + $quantity
                : $stockBefore - $quantity;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages(['quantity' => 'Le stock disponible est insuffisant.']);
            }

            return $this->createMovement($lockedProduct, $user, $type, $quantity, $stockAfter, $reference, $notes);
        });
    }

    private function lockActiveProduct(Product $product): Product
    {
        $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->getKey());

        if ($lockedProduct->status !== RecordStatus::Active) {
            throw ValidationException::withMessages(['product' => 'Un produit archive ne peut pas recevoir de mouvement.']);
        }

        return $lockedProduct;
    }

    private function createMovement(
        Product $product,
        User $user,
        MovementType $type,
        float $quantity,
        float $stockAfter,
        ?string $reference,
        ?string $notes,
    ): StockMovement {
        $stockBefore = (float) $product->current_stock;

        $movement = $product->stockMovements()->create([
            'user_id' => $user->getKey(),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference' => $reference,
            'notes' => $notes,
            'movement_date' => now(),
        ]);

        $product->forceFill(['current_stock' => $stockAfter])->save();
        $this->alertService->evaluate($product->refresh());

        return $movement;
    }
}
