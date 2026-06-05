<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsRun extends Model
{
    protected $fillable = [
        'user_id',
        'period_start',
        'period_end',
        'status',
        'is_current',
        'summary',
        'compiled_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'is_current' => 'boolean',
            'summary' => 'array',
            'compiled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productKpis(): HasMany
    {
        return $this->hasMany(AnalyticsProductKpi::class);
    }

    public function categoryKpis(): HasMany
    {
        return $this->hasMany(AnalyticsCategoryKpi::class);
    }

    public function supplierKpis(): HasMany
    {
        return $this->hasMany(AnalyticsSupplierKpi::class);
    }

    public function inventoryKpis(): HasMany
    {
        return $this->hasMany(AnalyticsInventoryKpi::class);
    }
}
