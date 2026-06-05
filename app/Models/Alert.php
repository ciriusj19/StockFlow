<?php

namespace App\Models;

use App\Enums\AlertStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    /** @use HasFactory<\Database\Factories\AlertFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'message',
        'status',
        'triggered_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AlertStatus::class,
            'triggered_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
