<?php

use App\Console\Commands\CheckDeviceStatus;
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

