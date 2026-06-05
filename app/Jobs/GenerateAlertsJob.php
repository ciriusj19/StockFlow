<?php

namespace App\Jobs;

use App\Enums\RecordStatus;
use App\Models\Product;
use App\Services\AlertService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAlertsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AlertService $alertService): void
    {
        Product::query()
            ->where('status', RecordStatus::Active->value)
            ->each(fn (Product $product) => $alertService->evaluate($product));
    }
}
