<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Models\Product;

class AlertService
{
    public function evaluate(Product $product): ?Alert
    {
        $openAlert = $product->alerts()
            ->whereIn('status', [AlertStatus::New->value, AlertStatus::Viewed->value])
            ->latest('triggered_at')
            ->first();

        if ((float) $product->current_stock <= (float) $product->critical_stock) {
            return $openAlert ?? $product->alerts()->create([
                'type' => 'critical_stock',
                'message' => "Le stock de {$product->name} a atteint le seuil critique.",
                'status' => AlertStatus::New,
                'triggered_at' => now(),
            ]);
        }

        if ($openAlert) {
            $openAlert->update([
                'status' => AlertStatus::Resolved,
                'resolved_at' => now(),
            ]);
        }

        return $openAlert;
    }

    public function markAsViewed(Alert $alert): Alert
    {
        if ($alert->status === AlertStatus::New) {
            $alert->update(['status' => AlertStatus::Viewed]);
        }

        return $alert->refresh();
    }

    public function resolve(Alert $alert): Alert
    {
        if ($alert->status !== AlertStatus::Resolved) {
            $alert->update([
                'status' => AlertStatus::Resolved,
                'resolved_at' => now(),
            ]);
        }

        return $alert->refresh();
    }
}
