<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSupplierKpi extends Model
{
    protected $fillable = [
        'analytics_run_id',
        'supplier_id',
        'supplier_name',
        'products_count',
        'critical_products_count',
        'open_alerts_count',
        'average_risk_score',
        'stock_value',
        'recommended_quantity_total',
    ];

    protected function casts(): array
    {
        return [
            'products_count' => 'integer',
            'critical_products_count' => 'integer',
            'open_alerts_count' => 'integer',
            'average_risk_score' => 'decimal:2',
            'stock_value' => 'decimal:2',
            'recommended_quantity_total' => 'decimal:2',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(AnalyticsRun::class, 'analytics_run_id');
    }
}
