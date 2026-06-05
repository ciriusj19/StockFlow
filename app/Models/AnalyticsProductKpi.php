<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsProductKpi extends Model
{
    protected $fillable = [
        'analytics_run_id',
        'product_id',
        'category_id',
        'supplier_id',
        'product_name',
        'sku',
        'category_name',
        'supplier_name',
        'unit',
        'stock',
        'critical_stock',
        'stock_value',
        'average_daily_usage',
        'remaining_days',
        'predicted_out_date',
        'risk_score',
        'risk_label',
        'recommended_quantity',
        'open_alerts_count',
        'resolved_alerts_count',
        'latest_alert_at',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:2',
            'critical_stock' => 'decimal:2',
            'stock_value' => 'decimal:2',
            'average_daily_usage' => 'decimal:4',
            'remaining_days' => 'decimal:2',
            'predicted_out_date' => 'date',
            'risk_score' => 'integer',
            'recommended_quantity' => 'decimal:2',
            'open_alerts_count' => 'integer',
            'resolved_alerts_count' => 'integer',
            'latest_alert_at' => 'datetime',
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

    public function riskBadgeTone(): string
    {
        return match (true) {
            $this->risk_score >= 100 => 'bg-rose-100 text-rose-800 ring-1 ring-inset ring-rose-200',
            $this->risk_score >= 75 => 'bg-orange-100 text-orange-800 ring-1 ring-inset ring-orange-200',
            $this->risk_score >= 50 => 'bg-amber-100 text-amber-800 ring-1 ring-inset ring-amber-200',
            default => 'bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-200',
        };
    }
}
