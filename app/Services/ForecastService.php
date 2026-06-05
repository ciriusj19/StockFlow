<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\RecordStatus;
use App\Models\Forecast;
use App\Models\Product;
use Carbon\CarbonInterface;

class ForecastService
{
    private const HISTORY_DAYS = 90;

    private const TARGET_DAYS = 60;

    public function generateForProduct(Product $product, ?CarbonInterface $asOf = null): Forecast
    {
        $asOf ??= now();

        $totalExits = (float) $product->stockMovements()
            ->where('type', MovementType::Exit->value)
            ->whereBetween('movement_date', [$asOf->copy()->subDays(self::HISTORY_DAYS), $asOf])
            ->sum('quantity');

        $averageDailyUsage = $totalExits / self::HISTORY_DAYS;
        $currentStock = (float) $product->current_stock;

        if ($averageDailyUsage === 0.0) {
            return $this->store($product, 0, null, 0, 25, $asOf);
        }

        $remainingDays = $currentStock / $averageDailyUsage;
        $predictedOutDate = $asOf->copy()->addDays((int) ceil($remainingDays))->toDateString();
        $recommendedQuantity = max(0, ($averageDailyUsage * self::TARGET_DAYS) - $currentStock);

        return $this->store(
            $product,
            $averageDailyUsage,
            $predictedOutDate,
            $recommendedQuantity,
            $this->riskScore($remainingDays),
            $asOf,
        );
    }

    public function generateAll(): void
    {
        Product::query()
            ->where('status', RecordStatus::Active->value)
            ->each(fn (Product $product) => $this->generateForProduct($product));
    }

    private function riskScore(float $remainingDays): int
    {
        return match (true) {
            $remainingDays <= 7 => 100,
            $remainingDays <= 15 => 75,
            $remainingDays <= 30 => 50,
            default => 25,
        };
    }

    private function store(
        Product $product,
        float $averageDailyUsage,
        ?string $predictedOutDate,
        float $recommendedQuantity,
        int $riskScore,
        CarbonInterface $generatedAt,
    ): Forecast {
        return $product->forecasts()->create([
            'average_daily_usage' => $averageDailyUsage,
            'predicted_out_date' => $predictedOutDate,
            'recommended_quantity' => $recommendedQuantity,
            'risk_score' => $riskScore,
            'generated_at' => $generatedAt,
        ]);
    }
}
