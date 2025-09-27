<?php

use App\Console\Commands\CheckDeviceStatus;
use App\Console\Commands\BackupUserData;
use App\Console\Commands\ProcessSocketSchedules;
use App\Console\Commands\ProcessBatchAnomalyDetection;
use App\Jobs\ProcessSocketSchedulesJob;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;


Schedule::command(CheckDeviceStatus::class)
    ->everyMinute()
    ->onFailure(function () {
        // Handle failure, e.g., send a notification or log the error
        Log::error('CheckDeviceStatus command failed.');
    });

Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->onFailure(function () {
        // Handle failure, e.g., send a notification or log the error
        Log::error('Queue work command failed.');
    });

Schedule::command('queue:work --queue=anomaly_detection --sleep=3 --tries=3 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(function () {
        // Handle failure
        Log::error('Anomaly detection queue worker failed.');
    });

// Run batch anomaly detection every hour for global outlier detection and LSTM error validation
Schedule::command(ProcessBatchAnomalyDetection::class, ['--hours=1'])
    ->hourly()
    ->onFailure(function () {
        Log::error('Batch anomaly detection command failed.');
    });

// Run daily comprehensive batch analysis for model drift detection
Schedule::command(ProcessBatchAnomalyDetection::class, ['--hours=24'])
    ->daily()
    ->at('02:00') // Run at 2 AM to avoid peak usage
    ->onFailure(function () {
        Log::error('Daily batch anomaly detection command failed.');
    });

// Process socket schedules every minute
Schedule::job(ProcessSocketSchedulesJob::class)
    ->everyMinute()
    ->onFailure(function () {
        Log::error('Socket schedules processing job failed.');
    });

// Alternative: Run the command directly instead of using a job
// Schedule::command(ProcessSocketSchedules::class)
//     ->everyMinute()
//     ->onFailure(function () {
//         Log::error('Socket schedules processing command failed.');
//     });

