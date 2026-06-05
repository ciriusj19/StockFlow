<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsInventoryKpi extends Model
{
    protected $fillable = [
        'analytics_run_id',
        'product_id',
        'product_name',
        'sku',
        'unit',
        'inventories_count',
        'total_expected_quantity',
        'total_difference',
        'absolute_difference',
        'positive_difference',
        'negative_difference',
        'reliability_score',
        'last_inventory_date',
    ];

    protected function casts(): array
    {
        return [
            'inventories_count' => 'integer',
            'total_expected_quantity' => 'decimal:2',
            'total_difference' => 'decimal:2',
            'absolute_difference' => 'decimal:2',
            'positive_difference' => 'decimal:2',
            'negative_difference' => 'decimal:2',
            'reliability_score' => 'decimal:2',
            'last_inventory_date' => 'date',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(AnalyticsRun::class, 'analytics_run_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
