<?php

use App\Jobs\GenerateAlertsJob;
use App\Jobs\GenerateForecastsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new GenerateForecastsJob)->dailyAt('00:00');
Schedule::job(new GenerateAlertsJob)->hourly();
