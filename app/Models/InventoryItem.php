<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'product_id',
        'expected_quantity',
        'actual_quantity',
        'difference',
    ];

    protected function casts(): array
    {
        return [
            'expected_quantity' => 'decimal:2',
            'actual_quantity' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
