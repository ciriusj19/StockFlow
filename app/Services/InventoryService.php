<?php

namespace App\Services;

use App\Enums\InventoryStatus;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function validate(Inventory $inventory, User $user): Inventory
    {
        return DB::transaction(function () use ($inventory, $user) {
            $lockedInventory = Inventory::query()
                ->with('items.product')
                ->lockForUpdate()
                ->findOrFail($inventory->getKey());

            if ($lockedInventory->status === InventoryStatus::Validated) {
                throw ValidationException::withMessages(['inventory' => 'Un inventaire valide ne peut plus etre modifie.']);
            }

            foreach ($lockedInventory->items as $item) {
                $difference = (float) $item->actual_quantity - (float) $item->expected_quantity;
                $item->update(['difference' => $difference]);

                if ($difference !== 0.0) {
                    $this->stockService->adjustTo(
                        $item->product,
                        $user,
                        (float) $item->actual_quantity,
                        "Inventaire #{$lockedInventory->getKey()}",
                    );
                }
            }

            $lockedInventory->update([
                'status' => InventoryStatus::Validated,
                'validated_at' => now(),
            ]);

            return $lockedInventory->refresh();
        });
    }
}
