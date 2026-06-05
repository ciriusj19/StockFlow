<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Forecast extends Model
{
    /** @use HasFactory<\Database\Factories\ForecastFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'average_daily_usage',
        'predicted_out_date',
        'recommended_quantity',
        'risk_score',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'average_daily_usage' => 'decimal:4',
            'predicted_out_date' => 'date',
            'recommended_quantity' => 'decimal:2',
            'risk_score' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function riskLabel(): string
    {
        return match (true) {
            $this->risk_score >= 100 => 'Critique',
            $this->risk_score >= 75 => 'Élevé',
            $this->risk_score >= 50 => 'Modéré',
            default => 'Faible',
        };
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

    public function riskTextTone(): string
    {
        return match (true) {
            $this->risk_score >= 100 => 'text-rose-700',
            $this->risk_score >= 75 => 'text-orange-700',
            $this->risk_score >= 50 => 'text-amber-700',
            default => 'text-emerald-700',
        };
    }
}
