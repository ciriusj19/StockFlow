<?php

namespace App\Jobs;

use App\Services\ForecastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateForecastsJob implements ShouldQueue
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
    public function handle(ForecastService $forecastService): void
    {
        $forecastService->generateAll();
    }
}
