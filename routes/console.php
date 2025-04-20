<?php

use App\Console\Commands\RunPredictionAnalysis;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(RunPredictionAnalysis::class)
    ->dailyAt('02:00')
    ->appendOutputTo(storage_path('logs/prediction-analysis.log'));